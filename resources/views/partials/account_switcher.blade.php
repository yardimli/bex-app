{{-- NEW: This partial creates a set of buttons for switching between personal and team accounts. --}}
{{-- It uses the DaisyUI 'join' component to group the buttons together. --}}
<div class="join">
	{{-- Personal Account Button --}}
	{{-- The 'btn-active' class is applied if there is no current team ID, indicating the personal account is active. --}}
	<button class="btn btn-sm join-item account-switch-btn {{ !$currentTeamId ? 'btn-active' : '' }}" data-team-id="0" title="{{ Auth::user()->name }}">
		<i class="bi bi-person-fill"></i>
		{{-- The user's name is shown on medium screens and larger for clarity. --}}
		<span class="hidden md:inline">{{ Auth::user()->name }}</span>
	</button>
	
	{{-- Team Account Buttons --}}
	{{-- Loop through each team the user is a member of. --}}
	@foreach($userTeams as $team)
		{{-- The 'btn-active' class is applied if the current team ID matches this team's ID. --}}
		<button class="btn btn-sm join-item account-switch-btn {{ $currentTeamId == $team->id ? 'btn-active' : '' }}" data-team-id="{{ $team->id }}" title="{{ $team->name }}">
			<i class="bi bi-people-fill"></i>
			{{-- The team name is shown on medium screens and larger. --}}
			<span class="hidden md:inline">{{ $team->name }}</span>
		</button>
	@endforeach
</div>
