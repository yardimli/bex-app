@extends('layouts.app')

{{-- Add specific CSS for chat bubbles if needed --}}
@push('styles')
	<style>
      .chat-history {
          height: calc(100vh - 380px);
		      width: 100%;
          overflow-y: auto;
          padding: 15px;
          display: flex;
          flex-direction: column;
      }
      .message-bubble {
          max-width: 75%;
          padding: 10px 15px;
          border-radius: 18px;
          margin-bottom: 10px;
          position: relative; /* For delete button */
      }
      .message-bubble.user {
          background-color: #0d6efd; /* Bootstrap primary */
          color: white;
          align-self: flex-end;
          border-bottom-right-radius: 5px;
      }
      .message-bubble.assistant {
          background-color: #e9ecef; /* Light grey */
          color: #212529;
          align-self: flex-start;
          border-bottom-left-radius: 5px;
      }
      /* Dark mode adjustments */
      html.dark-mode .message-bubble.assistant {
          background-color: #343a40;
          color: #e0e0e0;
      }
      html.dark-mode .message-bubble.user {
          /* Keep user bubble color consistent or choose another dark-mode friendly color */
          background-color: #0b5ed7; /* Slightly darker blue */
      }
      .message-meta {
          font-size: 0.75rem;
          color: #6c757d;
          margin-top: 5px;
      }
      html.dark-mode .message-meta {
          color: #adb5bd;
      }
      .message-bubble.user .message-meta {
          text-align: right;
      }
      .message-bubble.assistant .message-meta {
          text-align: left;
      }
      .delete-message-btn {
          position: absolute;
          top: -5px; /* Adjust positioning */
          right: -5px; /* Adjust positioning */
          background-color: rgba(255, 255, 255, 0.7);
          border-radius: 50%;
          padding: 2px 5px;
          font-size: 0.8rem;
          line-height: 1;
          cursor: pointer;
          color: #dc3545; /* Bootstrap danger */
          border: none;
          opacity: 0; /* Hide by default */
          transition: opacity 0.2s ease-in-out;
      }
      html.dark-mode .delete-message-btn {
          background-color: rgba(50, 50, 50, 0.8);
          color: #f8d7da; /* Lighter danger color */
      }
      .message-bubble.user:hover .delete-message-btn {
          opacity: 1; /* Show on hover of user bubble */
      }
      #chat-loader {
          text-align: center;
          padding: 10px;
          display: none; /* Hidden by default */
      }
	
	</style>
@endpush

@section('content')
	<!-- Content Header -->
	<div class="content-header">
		<div class="d-flex align-items-center me-3"> {{-- Wrap dropdown --}}
			@include('partials.dropdowns.mode_selector')
		</div>
		<h5 id="chat-title-display" class="mb-0 me-auto">
			{{ $activeChat ? Str::limit($activeChat->title, 50) : 'New Chat' }}
		</h5>
		<div class="d-flex align-items-center">
			{{-- Keep existing header icons --}}
			<a href="#" class="text-secondary me-3" title="Link" aria-label="Link"><i class="bi bi-link-45deg fs-5 icon-color"></i></a>
			<a href="#" class="text-secondary me-3" title="Settings" id="settingsButton" aria-label="Settings"><i class="bi bi-gear-fill fs-5 icon-color"></i></a>
			<a href="#" class="text-secondary me-3" title="Toggle Theme" id="themeToggleButton" aria-label="Toggle Theme"><i class="bi bi-brightness-high-fill fs-5 icon-color"></i></a>
			{{-- Auth Links --}}
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
						<li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
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
	
	<!-- Chat Area -->
	<div class="chat-area mt-3 flex-grow-1 d-flex flex-column">
		{{-- Chat History --}}
		<div id="chat-history-area" class="chat-history mb-3 flex-grow-1">
			@if($messages->isEmpty())
				<div class="text-center text-muted mt-5" id="empty-conversation">Start the conversation by typing below.</div>
			@else
				@foreach ($messages as $message)
					<div class="message-bubble {{ $message->role }}" id="message-{{ $message->id }}"  data-message-content="{!! nl2br(e($message->content)) !!}">
						{!! nl2br(e($message->content)) !!}
						
						@if ($message->role === 'user')
							<button class="delete-message-btn" title="Delete pair" data-message-id="{{ $message->id }}">
								<i class="bi bi-trash3-fill"></i>
							</button>
						@endif
						
						{{-- Show server creation time --}}
						<div class="message-meta">
							{{ $message->created_at->format('H:i') }} {{-- Format as HH:MM --}}
						</div>
						
						{{-- Add Action Buttons for Assistant --}}
						@if ($message->role === 'assistant')
							<div class="message-actions">
								<button class="btn btn-sm btn-outline-secondary copy-btn" title="Copy text"
								        data-message-id="{{ $message->id }}"
								        data-message-content="{{ $message->content }}"> {{-- Store raw content --}}
									<i class="bi bi-clipboard"></i>
								</button>
								<button class="btn btn-sm btn-outline-secondary read-aloud-btn" title="Read aloud"
								        data-message-id="{{ $message->id }}"
								        data-message-content="{{ $message->content }}"> {{-- Store raw content --}}
									<i class="bi bi-play-circle"></i>
									{{-- Spinner placeholder (hidden by default) --}}
									<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
								</button>
							</div>
						@endif
					</div>
				@endforeach
			@endif
		</div>
		{{-- Loading Indicator --}}
		<div id="chat-loader">
			<div class="spinner-border spinner-border-sm text-secondary" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
			<span class="ms-2 text-muted">Bex is thinking...</span>
		</div>
		
		{{-- Message Input Area --}}
		<div class="message-input-wrapper mt-auto w-100 px-3 pb-3"> {{-- Use w-100 and add padding --}}
			<div class="action-buttons">
				<button class="btn btn-outline-secondary" id="meetingSummaryButton"><i class="bi bi-calendar-check me-1"></i> Meeting Summary</button>
				<button class="btn btn-outline-secondary" id="actionItemsButton"><i class="bi bi-check2-square me-1"></i> My Action Items</button>
				<button class="btn btn-outline-secondary" id="myNotesButton"><i class="bi bi-journal-text me-1"></i> My Notes</button>
				<button class="btn btn-outline-secondary" id="myRecordingsButton"><i class="bi bi-mic me-1"></i> My Recordings</button>
				<button class="btn btn-outline-secondary" id="moreActionsButton"><i class="bi bi-three-dots me-1"></i> More</button> {{-- Consider adding functionality or removing if not used --}}
			</div>
			
			<form id="chat-input-form">
				@csrf {{-- Include CSRF token --}}
				{{-- Hidden input to store the current chat ID --}}
				<input type="hidden" id="chat_header_id" name="chat_header_id" value="{{ $activeChat?->id }}">
				
				<div class="message-input">
					<textarea class="form-control form-control-lg" id="message-input-field" name="message" placeholder="Message Bex..." rows="1" style="resize: none;" required>{{ $initialPrompt ? e($initialPrompt) : '' }}</textarea>
					<div class="message-input-icons">
						<i class="bi bi-paperclip icon-color" title="Attach file (Not implemented)"></i>
						<button type="submit" class="btn btn-link p-0 ms-0 mt-0 pt-0" style="border:0px; vertical-align: top;" id="send-message-button" title="Send">
							<i class="bi bi-send-fill fs-6 icon-color" style="font-size:0.9rem !important;"></i>
						</button>
						 <i class="bi bi-mic-fill icon-color ms-2" title="Voice input (Not implemented)"></i>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection

@push('scripts')
	{{-- Link the new chat JS file --}}
	<script src="{{ asset('js/chat.js') }}"></script>
@endpush
