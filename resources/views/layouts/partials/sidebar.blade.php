<div class="sidebar-inner">
    <ul class="nav flex-column mt-4">
        <li class="nav-item {{ request()->is('campaigns*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('campaigns.index') }}">
                <i class="fas fa-envelope mr-2"></i><span>{{ __('Campaigns') }}</span>
            </a>
        </li>
        @if (\Sendportal\Base\Facades\Helper::isPro())
        <li class="nav-item {{ request()->is('automations*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('automations.index') }}">
                <i class="fas fa-sync-alt mr-2"></i><span>{{ __('Automations') }}</span>
            </a>
        </li>
        @endif
        <li class="nav-item {{ request()->is('templates*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('templates.index') }}">
                <i class="fas fa-file-alt mr-2"></i> <span>{{ __('Templates') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('subscribers*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('subscribers.index') }}">
                <i class="fas fa-user mr-2"></i><span>{{ __('Subscribers') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('messages*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('messages.index') }}">
                <i class="fas fa-paper-plane mr-2"></i><span>{{ __('Messages') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('providers*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('providers.index') }}">
                <i class="fas fa-envelope mr-2"></i><span>{{ __('Providers') }}</span>
            </a>
        </li>
        @if ( auth()->user()->ownsCurrentWorkspace())
            <li class="nav-item {{ request()->is('settings*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('settings.index') }}">
                    <i class="fas fa-cog mr-2"></i><span>{{ __('Settings') }}</span>
                </a>
            </li>
        @endif
    </ul>
</div>
