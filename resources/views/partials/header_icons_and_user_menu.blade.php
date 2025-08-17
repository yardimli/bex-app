{{-- MODIFIED: This new partial contains the header icons and user menu, converted to DaisyUI --}}
<button class="btn btn-ghost btn-circle" title="Link">
	<i class="bi bi-link-45deg text-xl"></i>
</button>
<button class="btn btn-ghost btn-circle" id="settingsButton" title="Settings">
	<i class="bi bi-gear-fill text-xl"></i>
</button>
<label class="swap swap-rotate btn btn-ghost btn-circle" id="themeToggleButton" title="Toggle Theme">
	{{-- this hidden checkbox controls the state --}}
	{{-- MODIFIED: Added an ID to the checkbox for JS interaction --}}
	<input type="checkbox" class="theme-controller" value="dark" id="theme-controller-checkbox" />
	<i class="bi bi-brightness-high-fill swap-off text-xl"></i>
	<i class="bi bi-moon-stars-fill swap-on text-xl"></i>
</label>

@guest
	@if (Route::has('login'))
		{{-- MODIFIED: Replaced text link with an icon button for Login. --}}
        <a href="{{ route('login') }}" class="btn btn-ghost btn-circle" title="Login">
            <i class="bi bi-box-arrow-in-right text-xl"></i>
        </a>
	@endif
	@if (Route::has('register'))
		{{-- MODIFIED: Replaced text link with an icon button for Register. --}}
		<a href="{{ route('register') }}" class="btn btn-ghost btn-circle" title="Register">
			<i class="bi bi-person-plus-fill text-xl"></i>
		</a>
	@endif
@else
	{{-- Logged in User Dropdown --}}
	<div class="dropdown dropdown-end">
		<div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
			<div class="w-10 rounded-full">
                <img id="user-avatar-header" src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}'s avatar" />
			</div>
		</div>
		<ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
			<li>
				@if($currentTeamId && ($currentTeam = $userTeams->firstWhere('id', $currentTeamId)))
					<h2 class="menu-title text-truncate" title="{{ $currentTeam->name }}">
						<i class="bi bi-people-fill me-1"></i>
						<span>{{ $currentTeam->name }}</span>
					</h2>
				@else
					<h2 class="menu-title text-truncate" title="{{ Auth::user()->name }}">
						<i class="bi bi-person-fill me-1"></i>
						<span>{{ Auth::user()->name }}</span>
					</h2>
				@endif
			</li>
			<li>
				<a href="{{ route('messages.inbox') }}" class="justify-between">
					Inbox
					<span class="badge badge-error" id="unread-messages-count" style="display: none;"></span>
				</a>
			</li>
			<li><a href="{{ route('files.index') }}">My Files</a></li>
			<li><a href="{{ route('profile.edit') }}">Profile</a></li>
			<li><a href="{{ route('teams.index') }}">Teams</a></li>
			<div class="divider my-1"></div>
			<li>
				<details>
					<summary>Switch Account</summary>
					<ul id="account-switcher-submenu">
						<li @if(!$currentTeamId) class="bordered" @endif>
							<a href="#" data-team-id="0">
								<i class="bi bi-person-fill"></i>
								<span class="truncate">{{ Auth::user()->name }}</span>
								<div class="badge badge-neutral">Personal</div>
							</a>
						</li>
						@foreach($userTeams as $team)
							<li @if($currentTeamId == $team->id) class="bordered" @endif>
								<a href="#" data-team-id="{{ $team->id }}">
									<i class="bi bi-people-fill"></i>
									<span class="truncate">{{ $team->name }}</span>
									<div class="badge badge-info">Team</div>
								</a>
							</li>
						@endforeach
					</ul>
				</details>
			</li>
			<div class="divider my-1"></div>
			<li>
				<a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
					{{ __('Logout') }}
				</a>
				<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
			</li>
		</ul>
	</div>
@endguest
