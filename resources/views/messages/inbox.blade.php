@extends('layouts.app')

@push('styles')
    <style>
        .inbox-item {
            cursor: pointer;
            border-left: 4px solid transparent;
            transition: background-color 0.2s ease-in-out;
        }
        .inbox-item:hover {
            background-color: #f8f9fa;
        }
        html.dark-mode .inbox-item:hover {
            background-color: #343a40;
        }
        .inbox-item.unread {
            border-left-color: #0d6efd;
            font-weight: bold;
        }
        .sent-item .read-status {
            font-size: 0.8rem;
            color: #6c757d;
        }
        html.dark-mode .sent-item .read-status {
            color: #adb5bd;
        }
    </style>
@endpush

@section('content')
@include('partials.content_header')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Messaging</h1>
            <button class="btn btn-primary" id="compose-message-btn"><i class="bi bi-pencil-square me-1"></i> Compose Message</button>
        </div>

        <ul class="nav nav-tabs mb-3" id="inbox-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox-pane" type="button" role="tab" aria-controls="inbox-pane" aria-selected="true">Inbox</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent-pane" type="button" role="tab" aria-controls="sent-pane" aria-selected="false">Sent</button>
            </li>
        </ul>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-start align-items-center">
                <strong class="me-3">Filters:</strong>
                <div class="form-check me-4" id="unread-filter-container">
                    <input class="form-check-input" type="checkbox" value="" id="unread-filter">
                    <label class="form-check-label" for="unread-filter">
                        Unread Only
                    </label>
                </div>
                <div style="min-width: 250px;">
                    <select id="team-filter" class="form-select form-select-sm">
                        <option value="">All Teams</option>
                        @foreach($userTeams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="tab-content" id="inbox-tab-content">
            <!-- Inbox Pane -->
            <div class="tab-pane fade show active" id="inbox-pane" role="tabpanel" aria-labelledby="inbox-tab">
                <div class="list-group" id="inbox-list">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading messages...</p>
                    </div>
                </div>
                <div id="inbox-pagination-links" class="mt-3 d-flex justify-content-center"></div>
            </div>

            <!-- Sent Pane -->
            <div class="tab-pane fade" id="sent-pane" role="tabpanel" aria-labelledby="sent-tab">
                <div class="list-group" id="sent-list">
                    <!-- Sent items will be loaded here by JS -->
                </div>
                <div id="sent-pagination-links" class="mt-3 d-flex justify-content-center"></div>
            </div>
        </div>
    </div>

    <!-- Message Detail Modal -->
    <div class="modal fade" id="messageDetailModal" tabindex="-1" aria-labelledby="messageDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageDetailModalLabel">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="message-subject"></h6>
                    <div id="message-meta-info">
                        <!-- Meta info will be populated by JS -->
                    </div>
                    <hr>
                    <div id="message-body" style="white-space: pre-wrap;"></div>
                    <div id="recipient-status-list" class="mt-3" style="display: none;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    {{-- <button type="button" class="btn btn-primary">Reply</button> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/inbox.js') }}"></script>
@endpush
