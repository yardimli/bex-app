// public/js/teams.js:

$(document).ready(function() {
    const teamsList = $('#teams-list');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const createTeamModal = document.getElementById('createTeamModal');
    const addMemberModal = document.getElementById('addMemberModal');
    const teamAvatarCropModal = document.getElementById('teamAvatarCropModal');
    let myUserId = null;

    // renderTeamCard rewritten for DaisyUI card component
    function renderTeamCard(team, currentTeamId) {
        const isOwner = team.owner_id === myUserId;
        const isActive = team.id == currentTeamId;

        const avatarUploadButton = isOwner ? `
        <button class="btn btn-sm btn-circle bg-base-100 shadow-lg absolute upload-team-avatar-btn" title="Upload team avatar" style="right: -14px;">
            <i class="bi bi-camera-fill text-primary text-lg"></i>
        </button>
    ` : '';

        let membersHtml = team.team_members.map(member => `
        <li class="flex items-center">
            <i class="bi bi-person-fill me-2"></i> ${$('<div>').text(member.user.name).html()} ${member.role === 'owner' ? '<span class="badge badge-primary badge-sm ml-2">Owner</span>' : ''}
        </li>
    `).join('');

        return `
        <div class="col-span-1">
            <div class="card bg-base-100 shadow-xl h-full ${isActive ? 'ring-2 ring-primary' : ''}" data-team-id="${team.id}">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="avatar relative">
                            <div class="rounded-full overflow-hidden ring ring-primary ring-offset-base-100 ring-offset-2" style="width: 64px;">
                                <img src="${team.avatar_url}"
                                     alt="${$('<div>').text(team.name).html()}'s avatar"
                                     class="team-avatar-img w-full h-full object-cover" />
                            </div>
                            ${avatarUploadButton}
                        </div>
                        <div>
                            <h2 class="card-title">${$('<div>').text(team.name).html()}</h2>
                            <p class="text-base-content/ ৭০ text-sm">${$('<div>').text(team.description || 'No description.').html()}</p>
                        </div>
                    </div>
                    <h3 class="font-semibold mt-4">Members</h3>
                    <ul class="list-none p-0 space-y-2 text-sm">${membersHtml}</ul>
                </div>
                <div class="card-actions justify-between items-center p-4 bg-base-200">
                    <button class="btn btn-sm btn-outline btn-primary switch-team-btn ${isActive ? 'btn-disabled' : ''}">
                        <i class="bi bi-arrow-repeat me-1"></i> ${isActive ? 'Current Team' : 'Switch to Team'}
                    </button>
                    <div class="join">
                        ${isOwner ? `<button class="btn btn-sm btn-primary join-item add-member-btn" data-team-id="${team.id}" data-team-name="${$('<div>').text(team.name).html()}"> <i class="bi bi-person-plus-fill"></i> </button>` : ''}
                        <button class="btn btn-sm btn-outline btn-secondary join-item message-team-btn" data-team-id="${team.id}"> <i class="bi bi-chat-dots-fill"></i> </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    }


    function loadTeamsV2() {
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
                createTeamModal.close();
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
        addMemberModal.showModal();
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
                addMemberModal.close();
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

    // --- Team Avatar Upload Logic ---
    let cropper;
    let currentTeamIdForAvatar;
    const imageToCrop = document.getElementById('team-image-to-crop');
    const cropAndUploadBtn = document.getElementById('crop-and-upload-team-btn');

    // Hidden file input to trigger upload
    const fileInput = $('<input type="file" class="hidden" accept="image/*" />').appendTo('body');

    teamsList.on('click', '.upload-team-avatar-btn', function() {
        currentTeamIdForAvatar = $(this).closest('.card').data('team-id');
        fileInput.click();
    });

    fileInput.on('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            const reader = new FileReader();
            reader.onload = function(event) {
                imageToCrop.src = event.target.result;
                teamAvatarCropModal.showModal();
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1 / 1,
                    viewMode: 1,
                    background: false,
                    autoCropArea: 0.8,
                });
            };
            reader.readAsDataURL(file);
        }
        this.value = ''; // Reset input
    });

    if (teamAvatarCropModal) {
        teamAvatarCropModal.addEventListener('close', () => {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });
    }

    if (cropAndUploadBtn) {
        cropAndUploadBtn.addEventListener('click', function() {
            if (!cropper || !currentTeamIdForAvatar) {
                return;
            }
            this.disabled = true;
            this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Uploading...';

            cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                const formData = new FormData();
                formData.append('avatar', blob, 'avatar.png');

                $.ajax({
                    url: `/api/teams/${currentTeamIdForAvatar}/avatar`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    success: function(data) {
                        if (data.success) {
                            const newAvatarUrl = data.avatar_url + '?t=' + new Date().getTime(); // Cache bust
                            // Find the specific team card and update its image
                            $(`.card[data-team-id="${currentTeamIdForAvatar}"] .team-avatar-img`).attr('src', newAvatarUrl);
                            teamAvatarCropModal.close();
                        } else {
                            alert('Upload failed: ' + (data.message || 'An error occurred.'));
                        }
                    },
                    error: function(jqXHR) {
                        const error = jqXHR.responseJSON;
                        let errorMessage = error.message || 'An unknown error occurred.';
                        if (error.errors && error.errors.avatar) {
                            errorMessage = error.errors.avatar[0];
                        }
                        alert(`Upload failed: ${errorMessage}`);
                    },
                    complete: function() {
                        cropAndUploadBtn.disabled = false;
                        cropAndUploadBtn.innerHTML = 'Crop & Upload';
                    }
                });
            }, 'image/png');
        });
    }
    // --- END: Team Avatar Upload Logic ---


    loadTeamsV2();
});
