{{--resources/views/partials/sidebar.blade.php:--}}

{{-- MODIFIED: Rewritten with DaisyUI menu and Tailwind utility classes --}}
<aside class="bg-base-100 w-80 p-4 h-full flex flex-col">
	<div class="mb-4">
		<a href="{{ route('dashboard') }}"><img src="/images/logo-bex_02-logo-bex-color.png" alt="Bex Logo" class="h-10"></a>
	</div>

	<a href="{{ route('chat.show') }}" id="new-chat-button" class="btn btn-success w-full mb-3">
		<i class="bi bi-plus-lg me-1"></i> New Chat
	</a>

	<div class="lg:hidden mb-3">
		@include('partials.dropdowns.mode_selector', ['buttonClass' => 'w-full'])
	</div>

	<label class="input input-bordered flex items-center gap-2 mb-3">
		<i class="bi bi-search"></i>
		<input type="text" id="chat-search-input" class="grow" placeholder="Search Chats" />
	</label>

	<h2 class="menu-title">History</h2>
    <ul id="chat-history-list" class="menu flex-nowrap p-0 [&_li>*]:rounded-md mb-3 overflow-y-auto" style="max-height: calc(100vh - 400px);">
        {{-- This list will be populated by JavaScript --}}
        <li id="chat-history-loader"><span class="text-base-content/60">Loading history...</span></li>
        <li id="no-chat-history" style="display: none;"><span class="text-base-content/60">No chat history yet.</span></li>
        <li id="no-chat-results" style="display: none;"><span class="text-base-content/60">No chats found.</span></li>
    </ul>

	<h2 class="menu-title">Features</h2>
	<ul class="menu p-0 [&_li>*]:rounded-md">
		<li><button class="btn btn-outline w-full mb-2 justify-start" id="transcribeButton"><i class="bi bi-mic-fill me-2"></i> Transcribe</button></li>
		<li><button class="btn btn-outline w-full mb-2 justify-start" id="summarizeButton"><i class="bi bi-file-text-fill me-2"></i> Summarize</button></li>
        @php
            $isTeamContext = session('current_team_id');
        @endphp
        <li><button class="btn btn-outline w-full justify-start" id="teamWorkspaceButton"><i class="bi {{ $isTeamContext ? 'bi-people-fill' : 'bi-person-workspace' }} me-2"></i> {{ $isTeamContext ? 'Your Team Workspace' : 'My Workspace' }}</button></li>
	</ul>

	<div class="mt-auto"> <!-- Footer elements if any --> </div>
</aside>
