<!DOCTYPE html>
{{-- MODIFIED: Added data-theme for DaisyUI theme handling --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-team-id" content="{{ session('current_team_id') }}">
    <!-- Fonts & Icons -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    
    {{-- MODIFIED: Removed Bootstrap CSS, using Vite for Tailwind CSS --}}
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="min-h-screen bg-base-200">
{{-- MODIFIED: Replaced entire layout with DaisyUI Drawer for responsive sidebar --}}
<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col items-center justify-center">
        <!-- Page content here -->
        <main class="w-full h-screen flex flex-col p-4">
            @yield('content')
        </main>
    </div>
    <div class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        {{-- Include the refactored sidebar --}}
        @include('partials.sidebar')
    </div>
</div>

{{-- MODIFIED: All modals rewritten with DaisyUI <dialog> syntax --}}
@include('partials.modals.recent_meetings')
@include('partials.modals.my_notes')
@include('partials.modals.team_files')
@include('partials.modals.my_recordings')
@include('partials.modals.my_action_items')
@include('partials.modals.settings')
@include('partials.modals.summarize_content')
@include('partials.modals.transcribe')

<!-- Attach File Modal -->
<dialog id="attachFileModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Attach Files to Chat</h3>
        <div role="tablist" class="tabs tabs-lifted mt-4">
            <input type="radio" name="attach_file_tabs" role="tab" class="tab" aria-label="My Files" checked />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                <div id="attach-my-files-list" class="max-h-96 overflow-y-auto"></div>
            </div>
            
            <input type="radio" name="attach_file_tabs" role="tab" class="tab" aria-label="Team Files" />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                <div class="mb-3">
                    <select id="attach-team-select-filter" class="select select-bordered select-sm w-full max-w-xs"></select>
                </div>
                <div id="attach-team-files-list" class="max-h-96 overflow-y-auto"></div>
            </div>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-sm btn-ghost">Cancel</button>
                <button class="btn btn-sm btn-primary" id="confirm-attach-files-btn">Attach Selected</button>
            </form>
        </div>
    </div>
</dialog>

<!-- Image Preview Modal -->
<dialog id="imagePreviewModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl">
        <h3 class="font-bold text-lg">Image Preview</h3>
        <div class="py-4 text-center">
            <img id="image-preview-content" src="" alt="Image Preview" class="max-w-full h-auto inline-block">
        </div>
        <div class="modal-action">
            <form method="dialog"><button class="btn">Close</button></form>
        </div>
    </div>
</dialog>

<!-- PDF Preview Modal -->
<dialog id="pdfPreviewModal" class="modal">
    <div class="modal-box w-11/12 max-w-7xl h-5/6 p-0">
        <iframe id="pdf-preview-content" src="" class="w-full h-full border-none"></iframe>
        <div class="modal-action absolute top-2 right-4">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost">✕</button></form>
        </div>
    </div>
</dialog>

<!-- jQuery & Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@vite('resources/js/app.js')
<script src="{{ asset('js/ui.js') }}"></script>
<script src="{{ asset('js/action-items.js') }}"></script>
<script src="{{ asset('js/my-notes.js') }}"></script>
<script src="{{ asset('js/message-composer.js') }}"></script>
<script src="{{ asset('js/team-files.js') }}"></script>
@stack('scripts')
</body>
</html>
