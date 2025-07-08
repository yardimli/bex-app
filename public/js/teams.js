// public/js/teams.js:

$(document).ready(function() {
    const teamsList = $('#teams-list');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    // MODIFIED: Get dialog elements
    const createTeamModal = document.getElementById('createTeamModal');
    const addMemberModal = document.getElementById('addMemberModal');
    let myUserId = null;
    
    // MODIFIED: renderTeamCard rewritten for DaisyUI card component
    function renderTeamCard(team, currentTeamId) {
        const isOwner = team.owner_id === myUserId;
        const isActive = team.id == currentTeamId;
        
        let membersHtml = team.team_members.map(member => `
            <li class="flex items-center">
                <i class="bi bi-person-fill me-2"></i>
                ${$('<div>').text(member.user.name).html()}
                ${member.role === 'owner' ? '<span class="badge badge-primary badge-sm ml-2">Owner</span>' : ''}
            </li>
        `).join('');
        
        return `
            <div class="col-span-1">
                <div class="card bg-base-100 shadow-xl h-full ${isActive ? 'ring-2 ring-primary' : ''}" data-team-id="${team.id}">
                    <div class="card-body">
                        <h2 class="card-title">${$('<div>').text(team.name).html()}</h2>
                        <p class="text-base-content/70">${$('<div>').text(team.description || 'No description.').html()}</p>
                        <h3 class="font-semibold mt-2">Members</h3>
                        <ul class="list-none p-0 space-y-2 text-sm">${membersHtml}</ul>
                    </div>
                    <div class="card-actions justify-between items-center p-4 bg-base-200">
                        <button class="btn btn-sm btn-outline btn-primary switch-team-btn ${isActive ? 'btn-disabled' : ''}">
                            <i class="bi bi-arrow-repeat me-1"></i> ${isActive ? 'Current Team' : 'Switch to Team'}
                        </button>
                        <div class="join">
                            ${isOwner ? `<button class="btn btn-sm btn-primary join-item add-member-btn" data-team-id="${team.id}" data-team-name="${$('<div>').text(team.name).html()}">
                                <i class="bi bi-person-plus-fill"></i>
                            </button>` : ''}
                            <button class="btn btn-sm btn-outline btn-secondary join-item message-team-btn" data-team-id="${team.id}">
                                <i class="bi bi-chat-dots-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function loadTeamsV2() {
        // MODIFIED: DaisyUI loading spinner
        teamsList.html('<div class="col-span-full text-center p-5"><span class="loading loading-spinner loading-lg text-primary"></span><p class="mt-2">Loading your teams...</p></div>');
        $.ajax({
            url: '/api/user/teams',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                teamsList.empty();
                if (response.teams && response.teams.length > 0) {
                    myUserId = response.user_id;
                    const activeTeamId = response.current_team_id;
                    
                    response.teams.forEach(team => {
                        teamsList.append(renderTeamCard(team, activeTeamId));
                    });
                } else {
                    teamsList.html('<div class="col-span-full"><div class="alert alert-info">You are not part of any teams yet. Create one to get started!</div></div>');
                }
            },
            error: function() {
                teamsList.html('<div class="col-span-full"><div class="alert alert-error">Could not load your teams. Please try again later.</div></div>');
            }
        });
    }
    
    // Create Team
    $('#saveTeamButton').off('click').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span> Creating...');
        
        $.ajax({
            url: '/api/teams',
            method: 'POST',
            data: $('#createTeamForm').serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                createTeamModal.close(); // MODIFIED: Use .close()
                $('#createTeamForm')[0].reset();
                loadTeamsV2();
            },
            error: function(jqXHR) {
                alert('Error creating team: ' + (jqXHR.responseJSON?.message || 'Please check your input.'));
            },
            complete: function() {
                button.prop('disabled', false).html('Create Team');
            }
        });
    });
    
    // Open Add Member Modal
    teamsList.on('click', '.add-member-btn', function() {
        const teamId = $(this).data('team-id');
        const teamName = $(this).data('team-name');
        $('#addMemberTeamId').val(teamId);
        $('#addMemberTeamName').text(teamName);
        addMemberModal.showModal(); // MODIFIED: Use .showModal()
    });
    
    // Add Member
    $('#confirmAddMemberButton').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span> Adding...');
        const teamId = $('#addMemberTeamId').val();
        $.ajax({
            url: `/api/teams/${teamId}/members`,
            method: 'POST',
            data: $('#addMemberForm').serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                addMemberModal.close(); // MODIFIED: Use .close()
                $('#addMemberForm')[0].reset();
                loadTeamsV2();
            },
            error: function(jqXHR) {
                const errors = jqXHR.responseJSON?.errors;
                const message = jqXHR.responseJSON?.error;
                if (message) {
                    alert('Error: ' + message);
                } else if (errors && errors.email) {
                    alert('Error: ' + errors.email[0]);
                } else {
                    alert('An unknown error occurred.');
                }
            },
            complete: function() {
                button.prop('disabled', false).html('Add Member');
            }
        });
    });
    
    // Switch Team
    teamsList.on('click', '.switch-team-btn', function() {
        const teamId = $(this).closest('.card').data('team-id');
        const button = $(this);
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span> Switching...');
        
        $.ajax({
            url: '/api/user/current-team',
            method: 'POST',
            data: { team_id: teamId, _token: csrfToken },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert('Failed to switch team: ' + (response.error || 'Unknown error.'));
                    button.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Switch to Team');
                }
            },
            error: function() {
                alert('Failed to switch team.');
                button.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Switch to Team');
            }
        });
    });
    
    loadTeamsV2();
});
