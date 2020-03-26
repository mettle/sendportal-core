<?php

namespace Sendportal\Base\Http\Controllers\Subscriptions;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SubscriptionToggleRequest;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\MessageTenantRepository;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Sendportal\Base\Models\UnsubscribeEventType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionsController extends Controller
{
    /**
     * @var MessageTenantRepository
     */
    protected $messages;

    /**
     * SubscriptionsController constructor
     *
     * @param MessageTenantRepository $messages
     */
    public function __construct(MessageTenantRepository $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Unsubscribe a subscriber
     *
     * @param string $messageHash
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws \Exception
     */
    public function unsubscribe($messageHash)
    {
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        return view('sendportal::subscriptions.unsubscribe', compact('message'));
    }

    /**
     * Subscribe a subscriber
     *
     * @param string $messageHash
     * @return View
     */
    public function subscribe($messageHash)
    {
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        return view('sendportal::subscriptions.subscribe', compact('message'));
    }

    /**
     * Toggle subscriber subscription state
     *
     * @param SubscriptionToggleRequest $request
     * @param string $messageHash
     * @return RedirectResponse
     */
    public function update(SubscriptionToggleRequest $request, $messageHash)
    {
        $message = Message::where('hash', $messageHash)->first();
        $subscriber = $message->subscriber;

        $unsubscribed = (bool)$request->get('unsubscribed');

        if ($unsubscribed) {
            $message->unsubscribed_at = now();
            $message->save();

            $subscriber->unsubscribed_at = now();
            $subscriber->unsubscribe_event_id = UnsubscribeEventType::MANUAL_BY_SUBSCRIBER;
            $subscriber->save();

            return redirect()->route('subscriptions.subscribe', $message->hash)
                ->with('success', __('You have been successfully removed from the mailing list.'));
        }

        $message->unsubscribed_at = null;
        $message->save();

        $subscriber->unsubscribed_at = null;
        $subscriber->unsubscribe_event_id = null;
        $subscriber->save();

        return redirect()->route('subscriptions.unsubscribe', $message->hash)
            ->with('success', __('You have been added to the mailing list.'));
    }
}
