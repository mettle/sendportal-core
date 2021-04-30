<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\EmailServices;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\EmailServiceRequest;
use Sendportal\Base\Repositories\EmailServiceTenantRepository;

class EmailServicesController extends Controller
{
    /** @var EmailServiceTenantRepository */
    private $emailServices;

    public function __construct(EmailServiceTenantRepository $emailServices)
    {
        $this->emailServices = $emailServices;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $emailServices = $this->emailServices->all(Sendportal::currentWorkspaceId());

        return view('sendportal::email_services.index', compact('emailServices'));
    }

    public function create(): View
    {
        $emailServiceTypes = $this->emailServices->getEmailServiceTypes()->pluck('name', 'id');
        $quotaPeriods = $this->emailServices->getQuotaPeriods();

        return view('sendportal::email_services.create', compact('emailServiceTypes', 'quotaPeriods'));
    }

    /**
     * @throws Exception
     */
    public function store(EmailServiceRequest $request): RedirectResponse
    {
        $emailServiceType = $this->emailServices->findType($request->type_id);

        $settings = $request->get('settings', []);

        $this->emailServices->store(Sendportal::currentWorkspaceId(), [
            'name' => $request->name,
            'type_id' => $emailServiceType->id,
            'settings' => $settings,
        ]);

        return redirect()->route('sendportal.email_services.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $emailServiceId)
    {
        $emailServiceTypes = $this->emailServices->getEmailServiceTypes()->pluck('name', 'id');
        $quotaPeriods = $this->emailServices->getQuotaPeriods();
        $emailService = $this->emailServices->find(Sendportal::currentWorkspaceId(), $emailServiceId);
        $emailServiceType = $this->emailServices->findType($emailService->type_id);

        return view('sendportal::email_services.edit', compact('emailServiceTypes', 'emailService', 'emailServiceType', 'quotaPeriods'));
    }

    /**
     * @throws Exception
     */
    public function update(EmailServiceRequest $request, int $emailServiceId): RedirectResponse
    {
        $emailService = $this->emailServices->find(Sendportal::currentWorkspaceId(), $emailServiceId, ['type']);

        $settings = $request->get('settings');

        $emailService->name = $request->name;
        $emailService->settings = $settings;
        $emailService->save();

        return redirect()->route('sendportal.email_services.index');
    }

    /**
     * @throws Exception
     */
    public function delete(int $emailServiceId): RedirectResponse
    {
        $emailService = $this->emailServices->find(Sendportal::currentWorkspaceId(), $emailServiceId, ['campaigns']);

        if ($emailService->in_use) {
            return redirect()->back()->withErrors(__("You cannot delete an email service that is currently used by a campaign or automation."));
        }

        $this->emailServices->destroy(Sendportal::currentWorkspaceId(), $emailServiceId);

        return redirect()->route('sendportal.email_services.index');
    }

    public function emailServicesTypeAjax($emailServiceTypeId): JsonResponse
    {
        $emailServiceType = $this->emailServices->findType($emailServiceTypeId);
        $quotaPeriods = $this->emailServices->getQuotaPeriods();

        $view = view()
            ->make('sendportal::email_services.options.' . strtolower($emailServiceType->name), compact('quotaPeriods'))
            ->render();

        return response()->json([
            'view' => $view
        ]);
    }
}
