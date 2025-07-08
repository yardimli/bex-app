{{--resources/views/partials/sidebar.blade.php:--}}

{{-- MODIFIED: Rewritten with DaisyUI menu and Tailwind utility classes --}}
<aside class="bg-base-100 w-80 p-4 h-full flex flex-col">
	<div class="mb-4">
		<a href="{{ route('dashboard') }}"><img src="/images/logo-bex_02-logo-bex-color.png" alt="Bex Logo" class="h-10"></a>
	</div>
	
	<a href="{{ route('chat.show') }}" class="btn btn-success w-full mb-3">
		<i class="bi bi-plus-lg me-1"></i> New Chat
	</a>
	
	<label class="input input-bordered flex items-center gap-2 mb-3">
		<i class="bi bi-search"></i>
		<input type="text" class="grow" placeholder="Search Chats" />
	</label>
	
	<h2 class="menu-title">History</h2>
	{{-- MODIFIED: Added id="chat-history-list" for easier JS targeting and "flex-nowrap" to prevent multi-column layout. --}}
	<ul id="chat-history-list" class="menu flex-nowrap p-0 [&_li>*]:rounded-md mb-3 overflow-y-auto" style="max-height: calc(100vh - 350px);">
		@if(isset($chatHeaders) && $chatHeaders->isNotEmpty())
			@foreach ($chatHeaders as $header)
				<li>
					<a href="{{ route('chat.show', ['chatHeaderId' => $header->id]) }}"
					   id="chat-link-{{ $header->id }}"
					   title="{{ $header->title }}"
					   class="justify-between @if(isset($activeChat) && $activeChat->id == $header->id) active @endif">
						<span class="truncate">{{ Str::limit($header->title, 25) }}</span>
						<button class="btn btn-ghost btn-xs btn-circle delete-chat-btn" data-chat-id="{{ $header->id }}">
							<i class="bi bi-trash text-error"></i>
						</button>
					</a>
				</li>
			@endforeach
		@else
			<li><span class="text-base-content/60">No chat history yet.</span></li>
		@endif
	</ul>
	
	<h2 class="menu-title">Features</h2>
	<ul class="menu p-0 [&_li>*]:rounded-md">
		<li><button class="btn btn-outline w-full mb-2 justify-start" id="transcribeButton"><i class="bi bi-mic-fill me-2"></i> Transcribe</button></li>
		<li><button class="btn btn-outline w-full mb-2 justify-start" id="summarizeButton"><i class="bi bi-file-text-fill me-2"></i> Summarize</button></li>
		<li><button class="btn btn-outline w-full justify-start" id="teamWorkspaceButton"><i class="bi bi-people-fill me-2"></i> Your Team Workspace</button></li>
	</ul>
	
	<div class="mt-auto"> <!-- Footer elements if any --> </div>
</aside>
