@extends('layouts.app')

@section('content')
	{{-- MODIFIED: Replaced header with a DaisyUI navbar component --}}
	<div class="navbar bg-base-100 rounded-box shadow-sm mb-4">
		<div class="navbar-start">
			<label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
				<i class="bi bi-list text-xl"></i>
			</label>
			@include('partials.dropdowns.mode_selector')
		</div>
		<div class="navbar-center">
			<a class="btn btn-ghost text-xl" id="chat-title-display">
				{{ $activeChat ? Str::limit($activeChat->title, 30) : 'New Chat' }}
			</a>
		</div>
		<div class="navbar-end">
			@include('partials.header_icons_and_user_menu')
		</div>
	</div>
	
	<!-- Chat Area -->
	<div class="bg-base-100 rounded-box shadow-sm flex-grow flex flex-col p-4">
		{{-- Chat History --}}
		<div id="chat-history-area" class="chat-history flex-grow space-y-4">
			@if($messages->isEmpty())
				<div class="text-center text-base-content/60 mt-5" id="empty-conversation">Start the conversation by typing below.</div>
			@else
				@foreach ($messages as $message)
					{{-- MODIFIED: Using DaisyUI Chat Component structure --}}
					<div class="chat {{ $message->role === 'user' ? 'chat-end' : 'chat-start' }}" id="message-{{ $message->id }}" data-message-content="{!! e($message->content) !!}">
						<div class="chat-bubble {{ $message->role === 'user' ? 'chat-bubble-primary' : '' }} relative">
							@if($message->files->isNotEmpty())
								<div class="flex flex-wrap gap-2 mb-2">
									@foreach($message->files as $file)
										<a href="{{ route('api.files.download', $file) }}" class="badge badge-outline" title="Download {{ $file->original_filename }}">
											<i class="bi bi-file-earmark-arrow-down me-1"></i>
											{{ Str::limit($file->original_filename, 25) }}
										</a>
									@endforeach
								</div>
							@endif
							
							{!! nl2br(e($message->content)) !!}
							
							@if ($message->role === 'user')
								<button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn" title="Delete pair" data-message-id="{{ $message->id }}">
									<i class="bi bi-trash3-fill"></i>
								</button>
							@endif
						</div>
						<div class="chat-footer opacity-50">
							<time class="text-xs">{{ $message->created_at->format('H:i') }}</time>
						</div>
						@if ($message->role === 'assistant')
							<div class="chat-footer opacity-50">
								<button class="btn btn-ghost btn-xs copy-btn" title="Copy text" data-message-id="{{ $message->id }}"><i class="bi bi-clipboard"></i></button>
								<button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud" data-message-id="{{ $message->id }}">
									<i class="bi bi-play-circle"></i>
									<span class="loading loading-spinner loading-xs" style="display: none;"></span>
								</button>
							</div>
						@endif
					</div>
				@endforeach
			@endif
		</div>
		{{-- Loading Indicator --}}
		<div id="chat-loader" class="text-center p-2 hidden">
			<span class="loading loading-dots loading-md"></span>
		</div>
		
		{{-- Message Input Area --}}
		<div class="mt-auto w-full pt-4">
			<div class="flex flex-wrap justify-center gap-2 mb-4">
				<button class="btn btn-sm btn-outline" id="meetingSummaryButton"><i class="bi bi-calendar-check"></i> Meeting Summary</button>
				<button class="btn btn-sm btn-outline" id="actionItemsButton"><i class="bi bi-check2-square"></i> My Action Items</button>
				<button class="btn btn-sm btn-outline" id="myNotesButton"><i class="bi bi-journal-text"></i> My Notes</button>
				<button class="btn btn-sm btn-outline" id="myRecordingsButton"><i class="bi bi-mic"></i> My Recordings</button>
			</div>
			
			<form id="chat-input-form" class="w-full max-w-4xl mx-auto">
				@csrf
				<input type="hidden" id="chat_header_id" name="chat_header_id" value="{{ $activeChat?->id }}">
				<input type="hidden" id="attached-files-input" name="attached_files">
				<div id="file-pills-container" class="flex flex-wrap gap-2 mb-2"></div>
				<div class="form-control">
					<div class="join w-full">
						<button type="button" class="btn join-item" id="attach-file-btn"><i class="bi bi-paperclip text-xl"></i></button>
						<textarea class="textarea textarea-bordered join-item w-full" id="message-input-field" name="message" placeholder="Message Bex..." rows="1" style="resize: none;" required>{{ $initialPrompt ?? '' }}</textarea>
						<button type="submit" class="btn btn-primary join-item" id="send-message-button" title="Send">
							<i class="bi bi-send-fill text-xl"></i>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection

@push('scripts')
	<script src="{{ asset('js/chat.js') }}"></script>
@endpush
