<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Services\Content\MergeContent;
use Sendportal\Base\Services\Messages\DispatchMessage;

class MessagesController extends Controller
{
    /** @var MessageTenantRepositoryInterface */
    protected $messageRepo;

    /** @var DispatchMessage */
    protected $dispatchMessage;

    /** @var MergeContent */
    protected $mergeContent;

    public function __construct(
        MessageTenantRepositoryInterface $messageRepo,
        DispatchMessage $dispatchMessage,
        MergeContent $mergeContent
    ) {
        $this->messageRepo = $messageRepo;
        $this->dispatchMessage = $dispatchMessage;
        $this->mergeContent = $mergeContent;
    }

    /**
     * Show all sent messages.
     *
     * @throws Exception
     */
    public function index(): View
    {
        $params = request()->only(['search', 'status']);
        $params['sent'] = true;

        $messages = $this->messageRepo->paginateWithSource(
            auth()->user()->currentWorkspace()->id,
            'sent_atDesc',
            [],
            50,
            $params
        );

        return view('sendportal::messages.index', compact('messages'));
    }

    /**
     * Show draft messages.
     *
     * @throws Exception
     */
    public function draft(): View
    {
        $messages = $this->messageRepo->paginateWithSource(
            auth()->user()->currentWorkspace()->id,
            'created_atDesc',
            [],
            50,
            ['draft' => true]
        );

        return view('sendportal::messages.index', compact('messages'));
    }

    /**
     * Show a single message.
     *
     * @throws Exception
     */
    public function show(int $messageId): View
    {
        $message = $this->messageRepo->find(auth()->user()->currentWorkspace()->id, $messageId);

        $content = $this->mergeContent->handle($message);

        return view('sendportal::messages.show', compact('content', 'message'));
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function send(): RedirectResponse
    {
        if (!$message = $this->messageRepo->find(
            auth()->user()->currentWorkspace()->id,
            request('id'),
            ['subscriber']
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        if ($message->sent_at) {
            return redirect()->back()->withErrors(__('The selected message has already been sent'));
        }

        $this->dispatchMessage->handle($message);

        return redirect()->route('sendportal.messages.draft')->with(
            'success',
            __('The message was sent successfully.')
        );
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function delete(): RedirectResponse
    {
        if (!$message = $this->messageRepo->find(
            auth()->user()->currentWorkspace()->id,
            request('id')
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        $this->messageRepo->destroy(
            auth()->user()->currentWorkspace()->id,
            $message->id
        );

        return redirect()->route('sendportal.messages.draft')->with(
            'success',
            __('The message was deleted')
        );
    }

    /**
     * Send multiple messages.
     *
     * @throws Exception
     */
    public function sendSelected(): RedirectResponse
    {
        if (! request()->has('messages')) {
            return redirect()->back()->withErrors(__('No messages selected'));
        }

        if (!$messages = $this->messageRepo->getWhereIn(
            auth()->user()->currentWorkspace()->id,
            request('messages'),
            ['subscriber']
        )) {
            return redirect()->back()->withErrors(__('Unable to locate messages'));
        }

        $messages->each(function (Message $message) {
            if ($message->sent_at) {
                return;
            }

            $this->dispatchMessage->handle($message);
        });

        return redirect()->route('sendportal.messages.draft')->with(
            'success',
            __('The messages were sent successfully.')
        );
    }
}
