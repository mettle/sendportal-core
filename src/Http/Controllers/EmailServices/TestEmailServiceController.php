<?php

namespace Sendportal\Base\Http\Controllers\EmailServices;

use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\EmailServiceTestRequest;
use Sendportal\Base\Repositories\EmailServiceTenantRepository;
use Sendportal\Base\Services\Messages\DispatchTestMessage;
use Sendportal\Base\Services\Messages\MessageOptions;

class TestEmailServiceController extends Controller
{
    /** @var EmailServiceTenantRepository */
    private $emailServices;

    public function __construct(EmailServiceTenantRepository $emailServices)
    {
        $this->emailServices = $emailServices;
    }

    public function create(int $emailServiceId)
    {
        $emailService = $this->emailServices->find(Sendportal::currentWorkspaceId(), $emailServiceId);

        return view('sendportal::email_services.test', compact('emailService'));
    }

    /**
     * @throws Exception
     */
    public function store(int $emailServiceId, EmailServiceTestRequest $request, DispatchTestMessage $dispatchTestMessage): RedirectResponse
    {
        $emailService = $this->emailServices->find(Sendportal::currentWorkspaceId(), $emailServiceId);

        $options = new MessageOptions();
        $options->setFromEmail($request->input('from'));
        $options->setSubject($request->input('subject'));
        $options->setTo($request->input('to'));
        $options->setBody($request->input('body'));

        try {
            $messageId = $dispatchTestMessage->testService(Sendportal::currentWorkspaceId(), $emailService, $options);

            if (! $messageId) {
                return redirect()
                    ->back()
                    ->with(['error', __('Failed to dispatch test email.')]);
            }

            return redirect()
                ->route('sendportal.email_services.index')
                ->with(['success' => __('The test email has been dispatched.')]);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Response: ' . $e->getMessage());
        }
    }
}
