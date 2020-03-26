<div class="main-header">

    <header class="navbar navbar-expand flex-row justify-content-between pl-4-half pr-4-half py-3 mb-4">

        @guest
            <div class="container">
                <ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
                    <li class="nav-item mr-3">
                        <b><a class="nav-link text-dark" href="/register">{{ __('Register') }}</a></b>
                    </li>
                    <li class="nav-item">
                        <b><a class="nav-link text-dark" href="/login">{{ __('Login') }}</a></b>
                    </li>
                </ul>
            </div>
        @endguest

        @auth
            @if ( ! auth()->user()->hasVerifiedEmail())
                <div class="container">
                </div>
            @endif

            @if ( auth()->user()->hasVerifiedEmail())

                <button type="button" class="btn btn-light mr-3 btn-sm d-xl-none" data-toggle="modal" data-target="#sidebar-modal">
                    <i class="fal fa-bars"></i>
                </button>

                <h1 class="h4 mb-0 fc-dark-blue">@yield('heading')</h1>

                <ul class="navbar-nav flex-row ml-md-auto d-md-flex">
                    @php $workspaces = auth()->user()->workspaces @endphp

                    @if (count($workspaces) == 1)
                        <li class="nav-item mr-5 px-2">
                            <span class="nav-link" id="bd-versions" aria-haspopup="true" aria-expanded="false">
                                {{-- auth()->user()->currentWorkspace->name --}}
                            </span>
                        </li>
                    @elseif (count($workspaces) > 1 && auth()->user()->currentWorkspace)
                        <li class="nav-item dropdown mr-4 px-2 channel-dropdown">
                            <a class="nav-link dropdown-toggle fc-dark-blue" href="#" id="bd-versions"
                               data-toggle="dropdown"
                               aria-haspopup="true" aria-expanded="false">
                                {{ auth()->user()->currentWorkspace->name }}<i class="ml-2 fas fa-caret-down fc-gray-500"></i>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="bd-versions">
                                @foreach($workspaces as $workspace)
                                    <a class="dropdown-item px-3" href="{{ route('sendportal.workspaces.switch', $workspace->id) }}">
                                        <i class="fas fa-circle mr-2 {{ auth()->user()->currentWorkspace->id == $workspace->id ? 'fc-dark-blue' : 'fc-gray-300' }}"></i>{{ $workspace->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @endif

                    <li class="nav-item dropdown pl-3 user-dropdown">

                        <a class="nav-link dropdown-toggle mr-md-1 fc-dark-blue" href="#" id="bd-versions"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                           title="{{{ auth()->user()->full_name }}}">
                            <img src="{{{ auth()->user()->avatar }}}" height="25" class="rounded-circle mr-2"
                                 alt="{{ auth()->user()->name }}">
                            <span class="d-none d-sm-inline-block">{{{ \Illuminate\Support\Str::limit( auth()->user()->name, 25) }}}</span> <i
                                class="ml-2 fas fa-caret-down fc-gray-500"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="bd-versions">
                            <a class="dropdown-item px-3" href="{{ route('sendportal.profile.edit') }}"><i
                                    class="fas fa-user mr-2 fc-gray-300"></i>{{ __('My Profile') }}</a>
                            <a class="dropdown-item px-3" href="{{ route('sendportal.workspaces.index') }}"><i
                                    class="fas fa-layer-group mr-2 fc-gray-300"></i>{{ __('Workspaces') }}</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item px-3" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                    class="fas fa-sign-out-alt mr-2 fc-gray-300"></i>Log out</a>
                        </div>
                    </li>
                </ul>
                @endif
            @endauth
    </header>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

