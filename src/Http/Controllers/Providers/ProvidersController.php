<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Providers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\ProviderStoreRequest;
use Sendportal\Base\Http\Requests\ProviderUpdateRequest;
use Sendportal\Base\Repositories\ProviderTenantRepository;

class ProvidersController extends Controller
{
    /** @var ProviderTenantRepository */
    private $providers;

    public function __construct(ProviderTenantRepository $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $providers = $this->providers->all(auth()->user()->currentWorkspace()->id);

        return view('sendportal::providers.index', compact('providers'));
    }

    public function create(): View
    {
        $providerTypes = $this->providers->getProviderTypes()->pluck('name', 'id');

        return view('sendportal::providers.create', compact('providerTypes'));
    }

    /**
     * @throws Exception
     */
    public function store(ProviderStoreRequest $request): RedirectResponse
    {
        $providerType = $this->providers->findType($request->type_id);

        $settings = $request->get('settings');

        $this->providers->store(auth()->user()->currentWorkspace()->id, [
            'name' => $request->name,
            'type_id' => $providerType->id,
            'settings' => $settings,
        ]);

        return redirect()->route('sendportal.providers.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $providerId): View
    {
        $providerTypes = $this->providers->getProviderTypes()->pluck('name', 'id');
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId);
        $providerType = $this->providers->findType($provider->type_id);

        return view('sendportal::providers.edit', compact('providerTypes', 'provider', 'providerType'));
    }

    /**
     * @throws Exception
     */
    public function update(ProviderUpdateRequest $request, int $providerId): RedirectResponse
    {
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId, ['type']);

        $settings = $request->get('settings');

        $provider->name = $request->name;
        $provider->type_id = $request->type_id;
        $provider->settings = $settings;
        $provider->save();

        return redirect()->route('sendportal.providers.index');
    }

    /**
     * @throws Exception
     */
    public function delete(int $providerId): RedirectResponse
    {
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId, ['campaigns']);

        if ($provider->in_use) {
            return redirect()->back()->withErrors(__("You cannot delete a provider that is currently used by a campaign or automation."));
        }

        $this->providers->destroy(auth()->user()->currentWorkspace()->id, $providerId);

        return redirect()->route('sendportal.providers.index');
    }

    public function providersTypeAjax($providerTypeId): JsonResponse
    {
        $providerType = $this->providers->findType($providerTypeId);

        $view = view()
            ->make('sendportal::providers.options.' . strtolower($providerType->name))
            ->render();

        return response()->json([
            'view' => $view
        ]);
    }
}
