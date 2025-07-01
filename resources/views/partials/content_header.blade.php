@push('styles')
    <style>
        .dropdown-menu-left {
            left: auto !important;
            right: 100% !important;
            top: 0;
        }
        .selected {
            background-color: #e8ebf1;
        }
    </style>
@endpush
<div class="content-header">
    @include('partials.dropdowns.mode_selector')

    <div class="d-flex align-items-center">
        {{-- Right-side icons --}}
        <a href="#" class="text-secondary me-3" title="Link" aria-label="Link"><i class="bi bi-link-45deg fs-5 icon-color"></i></a>
        <a href="#" class="text-secondary me-3" title="Settings" id="settingsButton" aria-label="Settings"><i class="bi bi-gear-fill fs-5 icon-color"></i></a>
        <a href="#" class="text-secondary me-3" title="Toggle Theme" id="themeToggleButton" aria-label="Toggle Theme"><i class="bi bi-brightness-high-fill fs-5 icon-color"></i></a>

        {{-- Auth Links & User Dropdown --}}
        @guest
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="text-secondary me-3" title="Login" aria-label="Login"><i class="bi bi-box-arrow-in-right fs-5 icon-color"></i></a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-secondary" title="Register" aria-label="Register"><i class="bi bi-person-plus-fill fs-5 icon-color"></i></a>
            @endif
        @else
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-secondary text-decoration-none dropdown-toggle icon-color" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" title="{{ Auth::user()->name }}">
                    <i class="bi bi-person-circle fs-5 me-1"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser">
                    <li>
                        @if($currentTeamId && ($currentTeam = $userTeams->firstWhere('id', $currentTeamId)))
                            <h6 class="dropdown-header text-truncate" title="{{ $currentTeam->name }}"><i class="bi bi-people-fill me-1"></i> {{ $currentTeam->name }}</h6>
                        @else
                            <h6 class="dropdown-header text-truncate" title="{{ Auth::user()->name }}"><i class="bi bi-person-fill me-1"></i> {{ Auth::user()->name }}</h6>
                        @endif
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('messages.inbox') }}">Inbox <span class="badge bg-danger rounded-pill" id="unread-messages-count" style="display: none;"></span></a></li>
                    <li><a class="dropdown-item" href="{{ route('files.index') }}">My Files</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('teams.index') }}">Teams</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-submenu">
                        <a class="dropdown-item" href="#">Switch Account</a>
                        <ul class="dropdown-menu dropdown-menu-left" id="account-switcher-submenu">
                            <li @if(!$currentTeamId) class="selected" @endif>
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="#" data-team-id="0">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <i class="bi bi-person-fill me-2"></i>
                                        <span class="text-truncate" style="max-width: 150px;">{{ Auth::user()->name }}</span>
                                        <span class="badge bg-secondary ms-auto">Personal</span>
                                    </div>
                                </a>
                            </li>
                            @foreach($userTeams as $team)
                                <li @if($currentTeamId == $team->id) class="selected" @endif>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="#" data-team-id="{{ $team->id }}">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <i class="bi bi-people-fill me-2"></i>
                                            <span class="text-truncate" style="max-width: 150px;">{{ $team->name }}</span>
                                            <span class="badge bg-info ms-auto">Team</span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>
        @endguest
    </div>
</div>
