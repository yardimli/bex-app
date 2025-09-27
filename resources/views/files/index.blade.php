{{-- resources/views/files/index.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="p-4 flex flex-col h-full gap-4">
        @include('partials.page_header')

        <div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 flex flex-col min-h-0">
            <div class="flex justify-between items-center mb-4 flex-shrink-0">
                <h1 class="text-2xl font-bold">File Management</h1>
                <button class="btn btn-primary" id="openUploadModalBtn">
                    <i class="bi bi-upload me-2"></i>Upload New File
                </button>
            </div>

            <div role="tablist" class="tabs tabs-lifted tabs-lg flex-shrink-0">
                {{-- My Files Tab --}}
                <input type="radio" name="file_tabs" role="tab" class="tab" id="my-files-tab-radio" aria-label="My Files" checked />
                <input type="radio" name="file_tabs" role="tab" class="tab" id="team-files-tab-radio" aria-label="Team Files" />
            </div>
            <div class="tab-content-container flex-grow overflow-y-auto">
                {{-- My Files Pane --}}
                <div id="my-files-pane">
                    <div id="my-files-list">
                        {{-- My files will be loaded here by JS --}}
                    </div>
                </div>
                {{-- Team Files Pane --}}
                <div id="team-files-pane" style="display:none;">
                    <div class="p-3 border-b border-base-300">
                        <label for="team-select-filter" class="label">
                            <span class="label-text">Select a Team:</span>
                        </label>
                        <select id="team-select-filter" class="select select-bordered w-full max-w-xs"></select>
                    </div>
                    <div id="team-files-list">
                        {{-- Team files will be loaded here by JS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="uploadFileModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Upload File</h3>
            <form id="uploadFileForm" enctype="multipart/form-data" class="py-4">
                <input type="hidden" id="upload-team-id" name="team_id" value="">
                <p class="text-sm mb-2">Uploading to: <strong id="upload-destination">Your Files</strong></p>
                <div class="form-control w-full">
                    <label class="label" for="fileInput">
                        <span class="label-text">Select file (Max 10MB)</span>
                    </label>
                    <input type="file" id="fileInput" name="file" class="file-input file-input-bordered w-full" required>
                    <label class="label">
                        <span class="label-text-alt">Allowed: txt, doc, docx, pdf, jpg, png</span>
                    </label>
                </div>
                <div id="upload-progress-container" class="mt-3" style="display: none;">
                    <progress id="upload-progress-bar" class="progress progress-primary w-full" value="0" max="100">0%</progress>
                </div>
                <div id="upload-error" class="alert alert-error mt-3" style="display: none;"></div>
            </form>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
                <button type="button" class="btn btn-primary" id="submitUploadBtn">Upload</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <dialog id="shareFileModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Share File</h3>
            <p class="py-4">Share "<strong><span id="share-file-name"></span></strong>" with your teams:</p>
            <form id="shareFileForm">
                <input type="hidden" id="share-file-id" name="file_id">
                <div id="share-team-list" class="mb-3 max-h-60 overflow-y-auto">
                    {{-- Checkboxes for teams will be loaded here --}}
                </div>
                <div id="share-error" class="alert alert-error" style="display: none;"></div>
            </form>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Cancel</button>
                </form>
                <button type="button" class="btn btn-primary" id="submitShareBtn">Save Sharing Settings</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/files.js') }}"></script>
@endpush
