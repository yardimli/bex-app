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
    <meta name="current-user-id" content="{{ auth()->id() }}">
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
    <div class="drawer-content flex flex-col h-screen">

        {{-- This main area will hold the page content and grow, pushing the footer down --}}
        <main class="flex-grow min-h-0 overflow-y-auto">
            @yield('content')
        </main>

        {{-- ADDED: New persistent footer bar --}}
        <footer class="p-2 bg-base-200 border-t border-base-300 flex justify-between items-center text-sm">
            {{-- Left Side: Usage Analysis Button --}}
            <div>
                <button id="usage-log-button" class="btn btn-sm btn-ghost" title="View detailed usage logs">
                    <i class="bi bi-bar-chart-line-fill text-base"></i>
                    <span class="hidden md:inline">Usage Analysis</span>
                </button>
            </div>

            {{-- Right Side: User's Personal Stats --}}
            <div id="usage-stats-container" class="flex items-center gap-4 md:gap-6">
                <div class="text-right">
                    <div class="font-bold" id="total-prompt-tokens">...</div>
                    <div class="text-xs opacity-70">Prompt Tokens</div>
                </div>
                <div class="text-right">
                    <div class="font-bold" id="total-completion-tokens">...</div>
                    <div class="text-xs opacity-70">Completion Tokens</div>
                </div>
                <div class="text-right">
                    <div class="font-bold" id="total-cost">...</div>
                    <div class="text-xs opacity-70">Total Cost</div>
                </div>
            </div>
        </footer>
    </div>
    <div class="drawer-side">
        <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
        {{-- Include the refactored sidebar --}}
        @include('partials.sidebar')
    </div>
</div>

{{-- Usage Log Modal --}}
<dialog id="usage_log_modal" class="modal modal-bottom sm:items-center">
    <div class="modal-box w-11/12 max-w-6xl">
        <h3 class="font-bold text-lg">LLM Usage Logs</h3>
        <div id="usage-log-loader" class="text-center p-8">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
        <div id="usage-log-content" class="hidden">
            <div class="overflow-x-auto mt-4">
                <table class="table table-zebra table-sm w-full">
                    <thead>
                    <tr>
                        <th class="w-2/12 whitespace-nowrap">Timestamp</th>
                        <th class="w-1/12">User</th>
                        <th class="w-1/12">Team</th>
                        <th class="w-4/12">Model</th>
                        <th class="w-1/12 text-right whitespace-nowrap">Prompt Tokens</th>
                        <th class="w-1/12 text-right whitespace-nowrap">Comp. Tokens</th>
                        <th class="w-2/12 text-right whitespace-nowrap">Total Cost</th>
                    </tr>
                    </thead>
                    <tbody id="usage-log-table-body">
                    </tbody>
                </table>
            </div>
            <div id="usage-log-pagination" class="mt-4 flex justify-center"></div>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- MODIFIED: All modals rewritten with DaisyUI <dialog> syntax --}}
@include('partials.modals.recent_meetings')
@include('partials.modals.my_notes')
@include('partials.modals.team_files')
@include('partials.modals.my_recordings')
@include('partials.modals.my_action_items')
@include('partials.modals.settings')
@include('partials.modals.summarize_content')
@include('partials.modals.transcribe')
@include('partials.modals.compose_message')
@include('partials.modals.new_chat_options')
@include('partials.modals.group_chat_required')
@include('partials.modals.group_chat_setup')


<dialog id="confirmationModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg" id="confirmationModalTitle">Confirm Action</h3>
        <p class="py-4" id="confirmationModalText">Are you sure you want to proceed?</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-sm btn-ghost" id="confirmationModalCancel">Cancel</button>
                <button class="btn btn-sm btn-error" id="confirmationModalConfirm">Confirm</button>
            </form>
        </div>
    </div>
</dialog>

<!-- Attach File Modal -->
<dialog id="attachFileModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box flex flex-col max-h-[85vh]">
        <div class="flex-grow min-h-0 overflow-y-auto">
        <h3 class="font-bold text-lg">Attach Files to Chat</h3>
        <div role="tablist" class="tabs tabs-lifted mt-4">
            <input type="radio" name="attach_file_tabs" role="tab" class="tab" aria-label="My Files" checked />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                <div id="attach-my-files-list"></div>
            </div>

            <input type="radio" name="attach_file_tabs" role="tab" class="tab" aria-label="Team Files" />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                <div class="mb-3">
                    <select id="attach-team-select-filter" class="select select-bordered select-sm w-full max-w-xs"></select>
                </div>
                <div id="attach-team-files-list"></div>
            </div>
        </div>
    </div>
        <div class="modal-action flex-shrink-0 pt-4">
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
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg">Image Preview</h3>
        <div class="py-4 text-center">
            <img id="image-preview-content" src="" alt="Image Preview" class="max-w-full h-auto inline-block">
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- PDF Preview Modal -->
<dialog id="pdfPreviewModal" class="modal">
    <div class="modal-box w-11/12 max-w-7xl h-5/6 p-0">
        <iframe id="pdf-preview-content" src="" class="w-full h-full border-none"></iframe>
        <div class="modal-action absolute top-2 right-4">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost">✕</button></form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- jQuery & Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@vite('resources/js/app.js')
<script src="{{ asset('js/ui.js') }}"></script>
<script src="{{ asset('js/action-items.js') }}"></script>
<script src="{{ asset('js/my-notes.js') }}"></script>
<script src="{{ asset('js/message-composer.js') }}"></script>
<script src="{{ asset('js/team-files.js') }}"></script>
<script src="{{ asset('js/usage-log.js') }}"></script>
<script src="{{ asset('js/usage-footer.js') }}"></script>
@stack('scripts')
</body>
</html>
