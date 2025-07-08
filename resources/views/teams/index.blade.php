{{-- resources/views/teams/index.blade.php --}}

@extends('layouts.app')

@section('content')
    {{-- MODIFIED: Added a full-height flex container to manage the layout. --}}
    <div class="p-4 flex flex-col h-full gap-4">
        {{-- MODIFIED: Included the shared page header. --}}
        @include('partials.page_header')
        
        {{-- MODIFIED: Wrapped original content in a flex-grow container for proper layout and scrolling. --}}
        <div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">My Teams</h1>
                {{-- MODIFIED: Changed data-bs-* to onclick for DaisyUI modal --}}
                <button class="btn btn-primary" onclick="createTeamModal.showModal()">
                    <i class="bi bi-plus-circle me-1"></i> Create New Team
                </button>
            </div>
            
            {{-- MODIFIED: Replaced Bootstrap row with CSS Grid --}}
            <div id="teams-list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Teams will be loaded here by JavaScript -->
                <div class="col-span-full text-center p-5">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="mt-2">Loading your teams...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODIFIED: Create Team Modal converted to DaisyUI <dialog> -->
    <dialog id="createTeamModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Create a New Team</h3>
            <form id="createTeamForm" class="py-4 space-y-4">
                <div class="form-control">
                    <label class="label" for="teamName"><span class="label-text">Team Name</span></label>
                    <input type="text" id="teamName" name="name" required maxlength="255" class="input input-bordered w-full" />
                </div>
                <div class="form-control">
                    <label class="label" for="teamDescription"><span class="label-text">Description (Optional)</span></label>
                    <textarea id="teamDescription" name="description" rows="3" maxlength="1000" class="textarea textarea-bordered w-full"></textarea>
                </div>
            </form>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Cancel</button>
                </form>
                <button type="button" class="btn btn-primary" id="saveTeamButton">Create Team</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
    
    <!-- MODIFIED: Add Member Modal converted to DaisyUI <dialog> -->
    <dialog id="addMemberModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Add Member to <span id="addMemberTeamName"></span></h3>
            <form id="addMemberForm" class="py-4">
                <input type="hidden" id="addMemberTeamId" name="team_id">
                <div class="form-control">
                    <label class="label" for="memberEmail"><span class="label-text">User's Email Address</span></label>
                    <input type="email" id="memberEmail" name="email" required class="input input-bordered w-full" />
                </div>
            </form>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Cancel</button>
                </form>
                <button type="button" class="btn btn-primary" id="confirmAddMemberButton">Add Member</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/teams.js') }}"></script>
@endpush
