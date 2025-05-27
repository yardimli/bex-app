<aside class="sidebar" id="sidebar">
	<div class="mb-4">
		<a href="{{ route('dashboard') }}"><img src="/images/logo-bex_02-logo-bex-color.png" alt="Bex Logo" style="height: 40px;"></a>
	</div>
	
	{{-- New Chat Button --}}
	<a href="{{ route('chat.show') }}" class="btn btn-success w-100 mb-3">
		<i class="bi bi-plus-lg me-1"></i> New Chat
	</a>
	
	{{-- Search (optional functionality) --}}
	 <div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-search"></i></span>
			<input type="text" class="form-control" placeholder="Search Chats">
	</div>
	
	<h6>History</h6>
	<nav class="nav d-flex flex-column mb-3 overflow-auto" style="max-height: calc(100vh - 350px); flex-wrap: nowrap;">
		@if(isset($chatHeaders) && $chatHeaders->isNotEmpty())
			@foreach ($chatHeaders as $header)
				<a class="nav-link py-1 ps-3 pe-2 d-flex justify-content-between align-items-center @if(isset($activeChat) && $activeChat->id == $header->id) active fw-bold @endif"
				   href="{{ route('chat.show', ['chatHeaderId' => $header->id]) }}"
				   id="chat-link-{{ $header->id }}"
				   title="{{ $header->title }}">
                    <span class="text-truncate" style="max-width: 180px;">
	                    {{ Str::limit($header->title, 25) }}
                    </span>
					<i class="bi bi-trash text-danger small delete-chat-btn" data-chat-id="{{ $header->id }}"></i>
				</a>
			@endforeach
		@else
			<span class="nav-link text-muted small py-1 ps-3">No chat history yet.</span>
		@endif
	</nav>
	
	<h6>Features</h6>
	<button class="btn btn-outline-secondary w-100 mb-2" id="transcribeButton">
		<i class="bi bi-mic-fill me-2"></i> Transcribe
	</button>
	<button class="btn btn-outline-secondary w-100 mb-2" id="summarizeButton">
		<i class="bi bi-file-text-fill me-2"></i> Summarize
	</button>
	<button class="btn btn-outline-secondary w-100" id="teamWorkspaceButton">
		<i class="bi bi-people-fill me-2"></i> Your Team Workspace
	</button>
	
	<div class="mt-auto"> <!-- Footer elements if any --> </div>
</aside>
