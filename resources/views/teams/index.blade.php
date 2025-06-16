@extends('layouts.app')

@push('styles')
    <style>
        .team-card {
            transition: box-shadow .3s;
        }
        .team-card.active-team {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.5);
            border-color: #0d6efd;
        }
        .member-list {
            list-style-type: none;
            padding-left: 0;
        }
        .member-list li {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .member-list .badge {
            font-size: 0.75em;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Teams</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                <i class="bi bi-plus-circle me-1"></i> Create New Team
            </button>
        </div>

        <div id="teams-list" class="row g-4">
            <!-- Teams will be loaded here by JavaScript -->
            <div class="col-12 text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading your teams...</p>
            </div>
        </div>
    </div>
43
    <!-- Create Team Modal -->
    <div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTeamModalLabel">Create a New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createTeamForm">
                        <div class="mb-3">
                            <label for="teamName" class="form-label">Team Name</label>
                            <input type="text" class="form-control" id="teamName" name="name" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="teamDescription" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="teamDescription" name="description" rows="3" maxlength="1000"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTeamButton">Create Team</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Add Member to <span id="addMemberTeamName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addMemberForm">
                        <input type="hidden" id="addMemberTeamId" name="team_id">
                        <div class="mb-3">
                            <label for="memberEmail" class="form-label">User's Email Address</label>
                            <input type="email" class="form-control" id="memberEmail" name="email" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAddMemberButton">Add Member</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/teams.js') }}"></script>
@endpush
