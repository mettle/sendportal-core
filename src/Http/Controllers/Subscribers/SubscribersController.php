<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Subscribers;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Rap2hpoutre\FastExcel\FastExcel;
use Sendportal\Base\Events\SubscriberAddedEvent;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SubscriberRequest;
use Sendportal\Base\Models\UnsubscribeEventType;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscribersController extends Controller
{
    /** @var SubscriberTenantRepository */
    private $subscriberRepo;

    /** @var SegmentTenantRepository */
    private $segmentRepo;

    public function __construct(SubscriberTenantRepository $subscriberRepo, SegmentTenantRepository $segmentRepo)
    {
        $this->subscriberRepo = $subscriberRepo;
        $this->segmentRepo = $segmentRepo;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $subscribers = $this->subscriberRepo->paginate(auth()->user()->currentWorkspace()->id, 'email', [], 50,
            request()->all());

        return view('sendportal::subscribers.index', compact('subscribers'));
    }

    /**
     * @throws Exception
     */
    public function create(): View
    {
        $segments = $this->segmentRepo->pluck(auth()->user()->currentWorkspace()->id);
        $selectedSegments = [];

        return view('sendportal::subscribers.create', compact('segments', 'selectedSegments'));
    }

    /**
     * @throws Exception
     */
    public function store(SubscriberRequest $request): RedirectResponse
    {
        $data = $request->all();
        $data['unsubscribed_at'] = $request->has('subscribed') ? null : now();
        $data['unsubscribe_event_id'] = $request->has('subscribed') ? null : UnsubscribeEventType::MANUAL_BY_ADMIN;

        $subscriber = $this->subscriberRepo->store(auth()->user()->currentWorkspace()->id, $data);

        event(new SubscriberAddedEvent($subscriber));

        return redirect()->route('sendportal.subscribers.index');
    }

    /**
     * @throws Exception
     */
    public function show(int $id): View
    {
        $subscriber = $this->subscriberRepo->find(auth()->user()->currentWorkspace()->id, $id,
            ['segments', 'messages.source']);

        return view('sendportal::subscribers.show', compact('subscriber'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $subscriber = $this->subscriberRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $segments = $this->segmentRepo->pluck(auth()->user()->currentWorkspace()->id);
        $selectedSegments = $subscriber->segments->pluck('id', 'name');

        return view('sendportal::subscribers.edit', compact('subscriber', 'segments', 'selectedSegments'));
    }

    /**
     * @throws Exception
     */
    public function update(SubscriberRequest $request, int $id): RedirectResponse
    {
        $subscriber = $this->subscriberRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $data = $request->all();

        // updating subscriber from subscribed -> unsubscribed
        if (!$request->has('subscribed') && !$subscriber->unsubscribed_at) {
            $data['unsubscribed_at'] = now();
            $data['unsubscribe_event_id'] = UnsubscribeEventType::MANUAL_BY_ADMIN;
        } // updating subscriber from unsubscribed -> subscribed
        elseif ($request->has('subscribed') && $subscriber->unsubscribed_at) {
            $data['unsubscribed_at'] = null;
            $data['unsubscribe_event_id'] = null;
        }

        $this->subscriberRepo->update(auth()->user()->currentWorkspace()->id, $id, $data);

        return redirect()->route('sendportal.subscribers.index');
    }

    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    public function export()
    {
        $subscribers = $this->subscriberRepo->all(auth()->user()->currentWorkspace()->id, 'id');

        if (!$subscribers->count()) {
            return redirect()->route('sendportal.subscribers.index')->withErrors(__('There are no subscribers to export'));
        }

        return (new FastExcel($subscribers))
            ->download(sprintf('subscribers-%s.csv', date('Y-m-d-H-m-s')), static function ($subscriber) {
                return [
                    'id' => $subscriber->id,
                    'hash' => $subscriber->hash,
                    'email' => $subscriber->email,
                    'first_name' => $subscriber->first_name,
                    'last_name' => $subscriber->last_name,
                    'created_at' => $subscriber->created_at,
                ];
            });
    }
}
