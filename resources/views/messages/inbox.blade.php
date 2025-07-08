{{-- resources/views/messages/inbox.blade.php --}}

@extends('layouts.app')

@section('content')
    {{-- MODIFIED: Added a full-height flex container to manage the layout. --}}
    <div class="p-4 flex flex-col h-full gap-4">
        {{-- MODIFIED: Included the shared page header. --}}
        @include('partials.page_header')
        
        {{-- MODIFIED: Wrapped original content in a flex-grow container for proper layout and scrolling. --}}
        <div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Messaging</h1>
                <button class="btn btn-primary" id="compose-message-btn"><i class="bi bi-pencil-square me-1"></i> Compose Message</button>
            </div>
            
            {{-- MODIFIED: Converted nav-tabs to DaisyUI tabs --}}
            <div role="tablist" class="tabs tabs-lifted tabs-lg mb-4">
                <input type="radio" name="inbox_tabs" role="tab" class="tab" id="inbox-tab-radio" aria-label="Inbox" checked />
                <input type="radio" name="inbox_tabs" role="tab" class="tab" id="sent-tab-radio" aria-label="Sent" />
            </div>
            
            <!-- MODIFIED: Filters converted from card to a styled div -->
            <div class="p-4 bg-base-200 rounded-box flex flex-wrap items-center gap-4 mb-4">
                <strong class="me-3">Filters:</strong>
                <div class="form-control" id="unread-filter-container">
                    <label class="label cursor-pointer gap-2">
                        <span class="label-text">Unread Only</span>
                        <input type="checkbox" id="unread-filter" class="checkbox checkbox-sm" />
                    </label>
                </div>
                <div class="form-control" style="min-width: 250px;">
                    <select id="team-filter" class="select select-bordered select-sm">
                        <option value="">All Teams</option>
                        @foreach($userTeams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            {{-- MODIFIED: Tab content structure for DaisyUI tabs --}}
            <div class="tab-content-container">
                <!-- Inbox Pane -->
                <div id="inbox-pane" class="space-y-2">
                    <div id="inbox-list">
                        {{-- Initial loading state --}}
                        <div class="text-center p-5"><span class="loading loading-spinner loading-lg"></span></div>
                    </div>
                    <div id="inbox-pagination-links" class="mt-4 flex justify-center"></div>
                </div>
                
                <!-- Sent Pane (initially hidden by JS logic) -->
                <div id="sent-pane" class="space-y-2" style="display: none;">
                    <div id="sent-list">
                        {{-- Sent items will be loaded here by JS --}}
                    </div>
                    <div id="sent-pagination-links" class="mt-4 flex justify-center"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODIFIED: Message Detail Modal converted to DaisyUI <dialog> -->
    <dialog id="messageDetailModal" class="modal">
        <div class="modal-box w-11/12 max-w-3xl">
            <h3 class="font-bold text-lg" id="message-subject"></h3>
            <div id="message-meta-info" class="py-2">
                <!-- Meta info will be populated by JS -->
            </div>
            <hr class="border-base-300">
            <div id="message-body" class="py-4 whitespace-pre-wrap break-words"></div>
            <div id="recipient-status-list" class="mt-3" style="display: none;"></div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endsection

@push('scripts')
    {{-- MODIFIED: JS to handle tab visibility based on radio buttons --}}
    <script>
        $(document).ready(function() {
            const inboxPane = $('#inbox-pane');
            const sentPane = $('#sent-pane');
            
            function togglePanes() {
                if ($('#inbox-tab-radio').is(':checked')) {
                    inboxPane.show();
                    sentPane.hide();
                } else {
                    inboxPane.hide();
                    sentPane.show();
                }
            }
            $('input[name="inbox_tabs"]').on('change', togglePanes);
            togglePanes(); // Initial check
        });
    </script>
    <script src="{{ asset('js/inbox.js') }}"></script>
@endpush
