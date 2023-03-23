<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Services\Content\MergeContentService;
use Sendportal\Base\Services\Content\MergeSubjectService;
use Sendportal\Base\Services\Messages\DispatchMessage;

class MessagesController extends Controller
{
    /** @var MessageTenantRepositoryInterface */
    protected $messageRepo;

    /** @var DispatchMessage */
    protected $dispatchMessage;

    /** @var MergeContentService */
    protected $mergeContentService;

    /** @var MergeSubjectService */
    protected $mergeSubjectService;

    public function __construct(
        MessageTenantRepositoryInterface $messageRepo,
        DispatchMessage $dispatchMessage,
        MergeContentService $mergeContentService,
        MergeSubjectService $mergeSubjectService
    ) {
        $this->messageRepo = $messageRepo;
        $this->dispatchMessage = $dispatchMessage;
        $this->mergeContentService = $mergeContentService;
        $this->mergeSubjectService = $mergeSubjectService;
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
            Sendportal::currentWorkspaceId(),
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
            Sendportal::currentWorkspaceId(),
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
        $message = $this->messageRepo->find(Sendportal::currentWorkspaceId(), $messageId);

        $content = $this->mergeContentService->handle($message);
        $subject = $this->mergeSubjectService->handle($message);

        return view('sendportal::messages.show', compact('content', 'message', 'subject'));
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function send(): RedirectResponse
    {
        if (!$message = $this->messageRepo->find(
            Sendportal::currentWorkspaceId(),
            request('id'),
            ['subscriber']
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        if ($message->sent_at) {
            return redirect()->back()->withErrors(__('The selected message has already been sent'));
        }

        $mid = $this->dispatchMessage->handle($message);
        if(empty($mid)){
            return redirect()->route('sendportal.messages.draft')->with(
                'danger',
                __('An error occured')
            );
        }else{
            return redirect()->route('sendportal.messages.draft')->with(
                'success',
                __('The message was sent successfully.')
            );
        }

    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function delete(): RedirectResponse
    {
        if (!$message = $this->messageRepo->find(
            Sendportal::currentWorkspaceId(),
            request('id')
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        if ($message->sent_at) {
            return redirect()->back()->withErrors(__('A sent message cannot be deleted'));
        }

        $this->messageRepo->destroy(
            Sendportal::currentWorkspaceId(),
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
            Sendportal::currentWorkspaceId(),
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
