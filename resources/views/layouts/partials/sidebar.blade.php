<div class="sidebar-inner">
    <ul class="nav flex-column mt-4">
        <li class="nav-item {{ request()->is('campaigns*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('sendportal.campaigns.index') }}">
                <i class="fas fa-envelope mr-2"></i><span>{{ __('Campaigns') }}</span>
            </a>
        </li>
        @if (automationsEnable())
            <li class="nav-item {{ request()->is('automations*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sendportal.automations.index') }}">
                    <i class="fas fa-sync-alt mr-2"></i><span>{{ __('Automations') }}</span>
                </a>
            </li>
        @endif
        <li class="nav-item {{ request()->is('templates*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('sendportal.templates.index') }}">
                <i class="fas fa-file-alt mr-2"></i> <span>{{ __('Templates') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('subscribers*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('sendportal.subscribers.index') }}">
                <i class="fas fa-user mr-2"></i><span>{{ __('Subscribers') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('messages*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('sendportal.messages.index') }}">
                <i class="fas fa-paper-plane mr-2"></i><span>{{ __('Messages') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('providers*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('sendportal.providers.index') }}">
                <i class="fas fa-envelope mr-2"></i><span>{{ __('Providers') }}</span>
            </a>
        </li>
        @if (user()->ownsCurrentTeam())
            <li class="nav-item {{ request()->is('settings*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sendportal.settings.index') }}">
                    <i class="fas fa-cog mr-2"></i><span>{{ __('Settings') }}</span>
                </a>
            </li>
        @endif
    </ul>
</div>
