$(document).ready(function() {
    const teamsList = $('#teams-list');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let myUserId = null; // Will be set after first load

    function renderTeamCard(team, currentTeamId) {
        const isOwner = team.owner_id === myUserId;
        // const isActive = team.id == currentTeamId;

        let membersHtml = team.team_members.map(member => `
            <li>
                <i class="bi bi-person-fill me-2"></i>
                ${$('<div>').text(member.user.name).html()}
                ${member.role === 'owner' ? '<span class="badge bg-primary ms-2">Owner</span>' : ''}
            </li>
        `).join('');

        return `
            <div class="col-md-6 col-lg-4">
                <div class="card team-card" data-team-id="${team.id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title">${$('<div>').text(team.name).html()}</h5>
                        </div>
                        <p class="card-text text-muted">${$('<div>').text(team.description || 'No description.').html()}</p>
                        <h6>Members</h6>
                        <ul class="member-list">${membersHtml}</ul>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-primary switch-team-btn">
                            <i class="bi bi-arrow-repeat me-1"></i> Switch to this Team
                        </button>

                        <div class="btn-group">
                            ${isOwner ? `<button class="btn btn-sm btn-primary add-member-btn" data-team-id="${team.id}" data-team-name="${$('<div>').text(team.name).html()}">
                                <i class="bi bi-person-plus-fill"></i>
                            </button>` : ''}
                            <button class="btn btn-sm btn-outline-secondary message-team-btn" data-team-id="${team.id}">
                                <i class="bi bi-chat-dots-fill"></i>
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        `;
    }

    // Simplified loadTeams function for the new API response
    function loadTeamsV2() {
        $.ajax({
            url: '/api/user/teams',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                teamsList.empty();
                // Assuming we can get user ID from a meta tag or auth object if needed
                // For now, let's assume the owner check can be done with team.owner.id
                if (response.teams && response.teams.length > 0) {
                    // Get the definitive user ID and active team from the server response
                    myUserId = response.user_id;
                    const activeTeamId = response.current_team_id;

                    // The old, incorrect logic for guessing the user ID is no longer needed.

                    response.teams.forEach(team => {
                        // The renderTeamCard function will now use the correct myUserId
                        teamsList.append(renderTeamCard(team, activeTeamId));
                    });
                } else {
                    teamsList.html('<div class="col-12"><div class="alert alert-info">You are not part of any teams yet. Create one to get started!</div></div>');
                }
            },
            error: function() {
                teamsList.html('<div class="col-12"><div class="alert alert-danger">Could not load your teams. Please try again later.</div></div>');
            }
        });
    }

    // Create Team
    $('#saveTeamButton').off('click').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...');

        $.ajax({
            url: '/api/teams',
            method: 'POST',
            data: $('#createTeamForm').serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                $('#createTeamModal').modal('hide');
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
        $('#addMemberModal').modal('show');
    });

    // Add Member
    $('#confirmAddMemberButton').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        const teamId = $('#addMemberTeamId').val();

        $.ajax({
            url: `/api/teams/${teamId}/members`,
            method: 'POST',
            data: $('#addMemberForm').serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                $('#addMemberModal').modal('hide');
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
        const teamId = $(this).closest('.team-card').data('team-id');
        const button = $(this);
        button.prop('disabled', true);

        $.ajax({
            url: '/api/user/current-team',
            method: 'POST',
            data: { team_id: teamId },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                // Visually update active state without full reload
                $('.team-card').removeClass('active-team');
                $('.switch-team-btn').prop('disabled', false);
                button.closest('.team-card').addClass('active-team');
                button.prop('disabled', true);
                alert('Active team switched successfully!');
                // Or reload the page to reflect changes everywhere
                // window.location.reload();
            },
            error: function() {
                alert('Failed to switch team.');
                button.prop('disabled', false);
            }
        });
    });

    loadTeamsV2();
});
