@extends('layouts.app')

@push('styles')
    <style>
        .file-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #dee2e6;
            gap: 1rem;
        }
        .file-list-item:last-child {
            border-bottom: none;
        }
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-grow: 1;
            min-width: 0; /* Prevents flex item from overflowing */
        }
        .file-icon {
            font-size: 1.75rem;
        }
        .file-details {
            min-width: 0;
        }
        .file-details strong {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-meta, .sharing-status {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .sharing-status .badge {
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .badge .btn-close {
            vertical-align: middle;
            line-height: 1;
            font-size: 0.65em;
            padding: 0.25em;
            margin-left: 0.35rem;
        }
        .file-actions {
            flex-shrink: 0;
        }
        .file-actions .btn {
            margin-left: 0.5rem;
        }
        .nav-tabs .nav-link {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
@include('partials.content_header')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">File Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                <i class="bi bi-upload me-2"></i>Upload New File
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="fileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="my-files-tab" data-bs-toggle="tab" data-bs-target="#my-files" type="button" role="tab" aria-controls="my-files" aria-selected="true">My Files</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="team-files-tab" data-bs-toggle="tab" data-bs-target="#team-files" type="button" role="tab" aria-controls="team-files" aria-selected="false">Team Files</button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="fileTabsContent">
                    <div class="tab-pane fade show active" id="my-files" role="tabpanel" aria-labelledby="my-files-tab">
                        <div id="my-files-list" class="list-group list-group-flush">
                            {{-- My files will be loaded here by JS --}}
                        </div>
                    </div>
                    <div class="tab-pane fade" id="team-files" role="tabpanel" aria-labelledby="team-files-tab">
                        <div class="p-3 border-bottom">
                            <label for="team-select-filter" class="form-label">Select a Team:</label>
                            <select id="team-select-filter" class="form-select" style="max-width: 300px;"></select>
                        </div>
                        <div id="team-files-list" class="list-group list-group-flush">
                            {{-- Team files will be loaded here by JS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFileModalLabel">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadFileForm" enctype="multipart/form-data">
                        <input type="hidden" id="upload-team-id" name="team_id" value="">
                        <p class="text-muted small mb-2">Uploading to: <strong id="upload-destination">Your Files</strong></p>
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Select file (Max 10MB)</label>
                            <input class="form-control" type="file" id="fileInput" name="file" required>
                            <div class="form-text">Allowed types: txt, doc, docx, pdf, jpg, jpeg, png.</div>
                        </div>
                        <div id="upload-progress-bar" class="progress" style="display: none;">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div id="upload-error" class="alert alert-danger mt-3" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitUploadBtn">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Share File Modal -->
    <div class="modal fade" id="shareFileModal" tabindex="-1" aria-labelledby="shareFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareFileModalLabel">Share File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Share "<strong><span id="share-file-name"></span></strong>" with your teams:</p>
                    <form id="shareFileForm">
                        <input type="hidden" id="share-file-id" name="file_id">
                        <div id="share-team-list" class="mb-3">
                            {{-- Checkboxes for teams will be loaded here --}}
                        </div>
                        <div id="share-error" class="alert alert-danger" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitShareBtn">Save Sharing Settings</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('js/files.js') }}"></script>
@endpush
