<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-team-id" content="{{ session('current_team_id') }}">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    @stack('styles')
    <style>
        .dropdown-submenu {
            position: relative;
        }
        .dropdown-submenu .dropdown-menu {
            top: -5px; /* Adjust vertical alignment */
            left: 100%;
            margin-top: 0;
            display: none; /* Hidden by default */
        }
        /* Show on hover */
        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }
        /* Add a right-arrow to indicate a submenu */
        .dropdown-submenu > a.dropdown-item::after {
            content: '›';
            font-weight: bold;
            float: right;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="main-wrapper">
    <!-- Sidebar -->
    @include('partials.sidebar')
    <!-- Main Content -->
    <main class="main-content" id="main-content">
        {{-- Sidebar Toggle Button (positioned via CSS) --}}
        <button id="sidebarToggle" class="btn sidebar-toggle-btn shadow-sm" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        @yield('content')
    </main>
</div>
<div class="sidebar-backdrop"></div>
<!-- Modals -->
@include('partials.modals.recent_meetings')
@include('partials.modals.my_notes')
@include('partials.modals.team_files')
@include('partials.modals.my_recordings')
@include('partials.modals.my_action_items')
@include('partials.modals.settings')
@include('partials.modals.summarize_content')
@include('partials.modals.transcribe')

{{-- ADDED: Global file preview modals --}}
<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="image-preview-content" src="" alt="Image Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<!-- PDF Preview Modal -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="height: 90vh;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: calc(100% - 56px);">
                <iframe id="pdf-preview-content" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeMessageModal" tabindex="-1" aria-labelledby="composeMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeMessageModalLabel">Compose New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="composeMessageForm">
                    <div class="mb-3">
                        <label for="compose-team" class="form-label">Team</label>
                        <select class="form-select" id="compose-team" required>
                            <option value="" selected disabled>-- Select a Team --</option>
                            {{-- Teams will be loaded here by JS --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="compose-recipients" class="form-label">Recipients</label>
                        <select class="form-select" id="compose-recipients" multiple required size="5" disabled>
                            <option>-- Select a team first --</option>
                        </select>
                        <small class="text-muted">Hold Shift/Cmd to select multiple recipients.</small>
                    </div>
                    <div class="mb-3">
                        <label for="compose-subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="compose-subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="compose-body" class="form-label">Message</label>
                        <textarea class="form-control" id="compose-body" rows="6" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendMessageButton">Send Message</button>
            </div>
        </div>
    </div>
</div>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- Custom JS -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/action-items.js') }}"></script>
<script src="{{ asset('js/my-notes.js') }}"></script>
<script src="{{ asset('js/message-composer.js') }}"></script>
<script src="{{ asset('js/team-files.js') }}"></script>
@stack('scripts')
</body>
</html>
