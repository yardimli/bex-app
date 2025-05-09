@extends('layouts.app')

@section('content')
	<!-- Content Header -->
	<div class="content-header">
		@include('partials.dropdowns.mode_selector')
		
		<div class="d-flex align-items-center"> {{-- Wrap right-side icons for alignment --}}
			<a href="#" class="text-secondary me-3" title="Link" aria-label="Link"><i
					class="bi bi-link-45deg fs-5 icon-color"></i></a>
			<a href="#" class="text-secondary me-3" title="Settings" id="settingsButton" aria-label="Settings"><i
					class="bi bi-gear-fill fs-5 icon-color"></i></a>
			<a href="#" class="text-secondary me-3" title="Toggle Theme" id="themeToggleButton" aria-label="Toggle Theme"><i
					class="bi bi-brightness-high-fill fs-5 icon-color"></i></a> {{-- Added me-3 for spacing & icon-color --}}
			
			{{-- Start Auth Links --}}
			@guest
				@if (Route::has('login'))
					<a href="{{ route('login') }}" class="text-secondary me-3" title="Login" aria-label="Login">
						<i class="bi bi-box-arrow-in-right fs-5 icon-color"></i> {{-- Login Icon --}}
					</a>
				@endif
				
				@if (Route::has('register'))
					<a href="{{ route('register') }}" class="text-secondary" title="Register" aria-label="Register">
						<i class="bi bi-person-plus-fill fs-5 icon-color"></i> {{-- Register Icon --}}
					</a>
				@endif
			@else
				{{-- Logged in User Dropdown --}}
				<div class="dropdown">
					<a href="#" class="d-flex align-items-center text-secondary text-decoration-none dropdown-toggle icon-color"
					   id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" title="{{ Auth::user()->name }}">
						<i class="bi bi-person-circle fs-5 me-1"></i> {{-- Person Icon --}}
						{{-- Optionally show name: <span class="d-none d-sm-inline">{{ Str::limit(Auth::user()->name, 15) }}</span> --}}
					</a>
					<ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser">
						<li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
						</li> {{-- Added Profile Link --}}
						<li>
							<hr class="dropdown-divider">
						</li> {{-- Added Divider --}}
						<li>
							<a class="dropdown-item" href="{{ route('logout') }}"
							   onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
								{{ __('Logout') }}
							</a>
							<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
								@csrf
							</form>
						</li>
					</ul>
				</div>
			@endguest
			{{-- End Auth Links --}}
		</div>
	</div>
	
	<!-- Chat Area -->
	<div class="chat-area">
		<h2>How can Bex assist you today?</h2>
		<!-- Chat history would go here -->
		
		<div class="message-input-wrapper mt-auto w-75">
			<div class="action-buttons">
				<button class="btn btn-outline-secondary" id="meetingSummaryButton"><i class="bi bi-calendar-check me-1"></i>
					Meeting Summary
				</button>
				<button class="btn btn-outline-secondary" id="actionItemsButton"><i class="bi bi-check2-square me-1"></i> My
					Action Items
				</button>
				<button class="btn btn-outline-secondary" id="myNotesButton"><i class="bi bi-journal-text me-1"></i> My Notes
				</button>
				<button class="btn btn-outline-secondary" id="myRecordingsButton"><i class="bi bi-mic me-1"></i> My Recordings
				</button>
				<button class="btn btn-outline-secondary" id="moreActionsButton"><i class="bi bi-three-dots me-1"></i> More
				</button> {{-- Consider adding functionality or removing if not used --}}
			</div>
			<form id="dashboard-prompt-form" action="{{ route('chat.show') }}" method="GET">
				<div class="message-input">
					<input type="text" class="form-control form-control-lg" name="prompt" id="dashboard-prompt-input"
					       placeholder="Message Bex...">
					<div class="message-input-icons">
						<i class="bi bi-paperclip icon-color"></i>
						<i class="bi bi-globe icon-color"></i>
						<i class="bi bi-mic-fill icon-color"></i>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection
