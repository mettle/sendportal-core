<?php

namespace Sendportal\Base\Http\Controllers;

use Sendportal\Base\Http\Requests\ProviderStoreRequest;
use Sendportal\Base\Http\Requests\ProviderUpdateRequest;
use Sendportal\Base\Repositories\ProviderTenantRepository;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProvidersController extends Controller
{
    /**
     * @var ProviderTenantRepository
     */
    protected $providers;

    /**
     * ProviderController constructor.
     *
     * @param ProviderTenantRepository $providers
     */
    public function __construct(
        ProviderTenantRepository $providers
    ) {
        $this->providers = $providers;
    }

    /**
     * @return Factory|View
     * @throws Exception
     */
    public function index()
    {
        $providers = $this->providers->all(auth()->user()->currentWorkspace()->id);

        return view('sendportal::providers.index', compact('providers'));
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        $providerTypes = $this->providers->getProviderTypes()->pluck('name', 'id');

        return view('sendportal::providers.create', compact('providerTypes'));
    }

    /**
     * @return RedirectResponse
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

        return redirect()->route('providers.index');
    }

    /**
     * @return Factory|View
     * @throws Exception
     */
    public function edit(int $providerId)
    {
        $providerTypes = $this->providers->getProviderTypes()->pluck('name', 'id');
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId);
        $providerType = $this->providers->findType($provider->type_id);

        return view('sendportal::providers.edit', compact('providerTypes', 'provider', 'providerType'));
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(ProviderUpdateRequest $request, int $providerId): RedirectResponse
    {
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId, ['type']);

        $settings = $request->get('settings');

        $provider->name = $request->name;
        $provider->settings = $settings;
        $provider->save();

        return redirect()->route('providers.index');
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function delete(int $providerId): RedirectResponse
    {
        $provider = $this->providers->find(auth()->user()->currentWorkspace()->id, $providerId, ['campaigns']);

        if ($provider->in_use) {
            return redirect()->back()->withErrors(__("You cannot delete a provider that is currently used by a campaign or automation."));
        }

        $this->providers->destroy(auth()->user()->currentWorkspace()->id, $providerId);

        return redirect()->route('providers.index');
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
