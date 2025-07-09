@extends('layouts.app')

@section('content')
	{{-- MODIFIED: Adjusted padding and gap to be smaller on mobile (p-2, gap-2) and larger on medium screens and up (md:p-4, md:gap-4). --}}
	<div class="p-2 md:p-4 flex flex-col h-full gap-2 md:gap-4">
		{{-- MODIFIED: Replaced inline header with a reusable partial. --}}
		@include('partials.page_header')
		
		{{-- Item 2: Main Chat Area (grows to fill space and is a flex container for its children) --}}
		<div class="bg-base-100 rounded-box shadow-sm flex-grow flex flex-col p-4 min-h-0">
			{{-- Child 2a: Chat History (grows to fill space within this container, and scrolls internally) --}}
			<div id="chat-history-area" class="chat-history flex-grow space-y-4">
				@if($messages->isEmpty())
					<div class="text-center text-base-content/60 mt-5" id="empty-conversation">Start the conversation by typing
						below.
					</div>
				@else
					@foreach ($messages as $message)
						{{-- Using DaisyUI Chat Component structure --}}
						<div class="chat {{ $message->role === 'user' ? 'chat-end' : 'chat-start' }}"
						     id="message-{{ $message->id }}" data-message-content="{!! e($message->content) !!}">
							<div class="chat-bubble {{ $message->role === 'user' ? 'chat-bubble-primary' : '' }} relative">
								@if($message->files->isNotEmpty())
									<div class="flex flex-wrap gap-2 mb-2">
										@foreach($message->files as $file)
											<a href="{{ route('api.files.download', $file) }}" class="badge badge-outline"
											   title="Download {{ $file->original_filename }}">
												<i class="bi bi-file-earmark-arrow-down me-1"></i>
												{{ Str::limit($file->original_filename, 25) }}
											</a>
										@endforeach
									</div>
								@endif
								
								{!! nl2br(e($message->content)) !!}
								
								@if ($message->role === 'user')
									<button
										class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn"
										title="Delete pair" data-message-id="{{ $message->id }}">
										<i class="bi bi-trash3-fill"></i>
									</button>
								@endif
							</div>
							<div class="chat-footer opacity-50">
								<time class="text-xs">{{ $message->created_at->format('H:i') }}</time>
								@if ($message->role === 'assistant')
									<button class="btn btn-ghost btn-xs copy-btn" title="Copy text" data-message-id="{{ $message->id }}">
										<i class="bi bi-clipboard"></i></button>
									<button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud"
									        data-message-id="{{ $message->id }}">
										<i class="bi bi-play-circle"></i>
										<span class="loading loading-spinner loading-xs" style="display: none;"></span>
									</button>
								@endif
							</div>
						</div>
					@endforeach
				@endif
			</div>
			
			{{-- Child 2b: Loading Indicator --}}
			<div id="chat-loader" class="text-center p-2 hidden">
				<span class="loading loading-dots loading-md"></span>
			</div>
			
			{{-- Child 2c: Message Input Area (pushed to the bottom by chat history's flex-grow) --}}
			{{-- MODIFIED: Removed the 'mt-auto' class from the previous attempt as it's not needed. The flex-grow on the history area handles positioning. --}}
			<div class="w-full pt-4">
				{{-- MODIFIED: Added a container with overflow-x-auto and a custom scrollbar-hide utility to allow horizontal dragging of buttons on small screens. --}}
				<div class="overflow-x-auto pb-2 mb-2 scrollbar-hide">
					{{-- MODIFIED: Changed to flex-nowrap to keep buttons on a single line. Added padding for scroll spacing and flex-shrink-0 to buttons. --}}
					<div class="flex flex-nowrap justify-start md:justify-center gap-2 px-2">
						<button class="btn btn-sm btn-outline flex-shrink-0" id="meetingSummaryButton"><i class="bi bi-calendar-check"></i> Meeting
							Summary
						</button>
						<button class="btn btn-sm btn-outline flex-shrink-0" id="actionItemsButton"><i class="bi bi-check2-square"></i> My Action
							Items
						</button>
						<button class="btn btn-sm btn-outline flex-shrink-0" id="myNotesButton"><i class="bi bi-journal-text"></i> My Notes</button>
						<button class="btn btn-sm btn-outline flex-shrink-0" id="myRecordingsButton"><i class="bi bi-mic"></i> My Recordings
						</button>
					</div>
				</div>
				
				<form id="chat-input-form" class="w-full max-w-4xl mx-auto">
					@csrf
					<input type="hidden" id="chat_header_id" name="chat_header_id" value="{{ $activeChat?->id }}">
					<input type="hidden" id="attached-files-input" name="attached_files">
					<div id="file-pills-container" class="flex flex-wrap gap-2 mb-2"></div>
					<div class="form-control">
						<div class="join w-full">
							<button type="button" class="btn join-item" id="attach-file-btn"><i class="bi bi-paperclip text-xl"></i>
							</button>
							<textarea class="textarea textarea-bordered join-item w-full" id="message-input-field" name="message"
							          placeholder="Message Bex..." rows="1" style="resize: none;"
							          required>{{ $initialPrompt ?? '' }}</textarea>
							<button type="submit" class="btn btn-primary join-item" id="send-message-button" title="Send">
								<i class="bi bi-send-fill text-xl"></i>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script src="{{ asset('js/chat.js') }}"></script>
@endpush