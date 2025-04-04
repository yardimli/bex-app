@extends('layouts.app')

@section('content')
	<!-- Content Header -->
	<div class="content-header">
		@include('partials.dropdowns.mode_selector')
		
		<div>
			<a href="#" class="text-secondary me-3" title="Link" aria-label="Link"><i class="bi bi-link-45deg fs-5"></i></a>
			<a href="#" class="text-secondary me-3" title="Settings" id="settingsButton" aria-label="Settings"><i class="bi bi-gear-fill fs-5"></i></a>
			<a href="#" class="text-secondary" title="Toggle Theme" id="themeToggleButton" aria-label="Toggle Theme"><i class="bi bi-brightness-high-fill fs-5"></i></a>
		</div>
	</div>
	
	<!-- Chat Area -->
	<div class="chat-area">
		<h2>How can Bex assist you today?</h2>
		
		<!-- Chat history would go here -->
		
		<div class="message-input-wrapper mt-auto w-75">
			<div class="action-buttons">
				<button class="btn btn-outline-secondary" id="meetingSummaryButton"><i class="bi bi-calendar-check me-1"></i> Meeting Summary</button>
				<button class="btn btn-outline-secondary" id="actionItemsButton"><i class="bi bi-check2-square me-1"></i> My Action Items</button>
				<button class="btn btn-outline-secondary" id="myNotesButton"><i class="bi bi-journal-text me-1"></i> My Notes</button>
				<button class="btn btn-outline-secondary" id="myRecordingsButton"><i class="bi bi-mic me-1"></i> My Recordings</button>
				<button class="btn btn-outline-secondary" id="moreActionsButton"><i class="bi bi-three-dots me-1"></i> More</button>
			</div>
			<div class="message-input">
				<input type="text" class="form-control form-control-lg" placeholder="Message Bex">
				<div class="message-input-icons">
					<i class="bi bi-paperclip"></i>
					<i class="bi bi-globe"></i>
					<i class="bi bi-mic-fill"></i>
				</div>
			</div>
		</div>
	</div>
@endsection
