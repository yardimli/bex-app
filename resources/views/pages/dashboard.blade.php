{{-- resources/views/pages/dashboard.blade.php --}}

@extends('layouts.app')

@section('content')
	{{-- MODIFIED: Added a full-height flex container to manage the layout, padding, and spacing. --}}
	<div class="p-4 flex flex-col h-full gap-4">
		{{-- MODIFIED: Replaced inline header with a reusable partial. --}}
		@include('partials.page_header')
		
		{{-- MODIFIED: Main content area now correctly grows to fill the remaining space and has a min-height of 0 to work within the flex layout. --}}
		<div class="bg-base-100 rounded-box shadow-sm flex-grow flex flex-col justify-center items-center p-4 min-h-0">
			<h2 class="text-3xl font-bold mb-8 text-center">How can Bex assist you today?</h2>
			
			<div class="w-full max-w-3xl mx-auto mt-auto">
				{{-- Action buttons --}}
				<div class="flex flex-wrap justify-center gap-2 mb-4">
					<button class="btn btn-sm btn-outline" id="meetingSummaryButton"><i class="bi bi-calendar-check"></i> Meeting Summary</button>
					<button class="btn btn-sm btn-outline" id="actionItemsButton"><i class="bi bi-check2-square"></i> My Action Items</button>
					<button class="btn btn-sm btn-outline" id="myNotesButton"><i class="bi bi-journal-text"></i> My Notes</button>
					<button class="btn btn-sm btn-outline" id="myRecordingsButton"><i class="bi bi-mic"></i> My Recordings</button>
				</div>
				
				{{-- Prompt input form --}}
				<form id="dashboard-prompt-form">
					@csrf
					<input type="hidden" id="attached-files-input" name="attached_files">
					<div id="file-pills-container" class="flex flex-wrap gap-2 mb-2">
						{{-- Attached file pills will be rendered here by JS --}}
					</div>
					<div class="form-control">
						{{-- MODIFIED: Replaced complex input with a clean DaisyUI 'join' component --}}
						<div class="join w-full">
							<button type="button" class="btn join-item" id="attach-file-btn" title="Attach file">
								<i class="bi bi-paperclip text-xl"></i>
							</button>
							<input type="text" class="input input-bordered join-item w-full text-lg" name="prompt" id="dashboard-prompt-input" placeholder="Message Bex...">
							<button type="submit" class="btn btn-primary join-item" id="dashboard-send-button" title="Send">
								<i class="bi bi-send-fill text-xl"></i>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
