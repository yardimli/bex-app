$(document).ready(function() {
    const myFilesList = $('#my-files-list');
    const teamFilesList = $('#team-files-list');
    const teamSelectFilter = $('#team-select-filter');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const uploadFileModal = new bootstrap.Modal(document.getElementById('uploadFileModal'));
    const shareFileModal = new bootstrap.Modal(document.getElementById('shareFileModal'));
    let userTeams = [];

    function getFileIcon(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-muted';
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-danger';
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-primary';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-info';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-secondary';
        return 'bi-file-earmark-fill text-muted';
    }

    function formatBytes(bytes, decimals = 2) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function renderMyFileItem(file) {
        let sharedWithHtml = '<span class="text-muted">Private</span>';
        if (file.shared_with_teams.length > 0) {
            sharedWithHtml = file.shared_with_teams.map(team => `
                <span class="badge bg-secondary">
                    ${$('<div>').text(team.name).html()}
                    <button class="btn-close btn-close-white revoke-share-btn" data-team-id="${team.id}" data-team-name="${$('<div>').text(team.name).html()}" aria-label="Revoke share"></button>
                </span>
            `).join(' ');
        }

        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            previewButtonHtml = `<button class="btn btn-sm btn-outline-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }

        return `
            <div class="file-list-item" data-file-id="${file.id}">
                <div class="file-info">
                    <i class="bi ${getFileIcon(file.mime_type)} file-icon"></i>
                    <div class="file-details">
                        <strong>${$('<div>').text(file.original_filename).html()}</strong>
                        <div class="file-meta">${formatBytes(file.size)} • Uploaded ${new Date(file.created_at).toLocaleDateString()}</div>
                        <div class="sharing-status mt-1">${sharedWithHtml}</div>
                    </div>
                </div>
                <div class="file-actions">
                    ${previewButtonHtml}
                    <button class="btn btn-sm btn-outline-primary share-btn" title="Share"><i class="bi bi-share-fill"></i></button>
                    <a href="/api/files/${file.id}/download" class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </div>`;
    }

    function renderTeamFileItem(file) {
        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            previewButtonHtml = `<button class="btn btn-sm btn-outline-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }
        return `
            <div class="file-list-item" data-file-id="${file.id}">
                <div class="file-info">
                    <i class="bi ${getFileIcon(file.mime_type)} file-icon"></i>
                    <div class="file-details">
                        <strong>${$('<div>').text(file.original_filename).html()}</strong>
                        <div class="file-meta">
                            ${formatBytes(file.size)} • Shared by ${$('<div>').text(file.owner.name).html()} on ${new Date(file.pivot.shared_at).toLocaleDateString()}
                        </div>
                    </div>
                </div>
                <div class="file-actions">
                    ${previewButtonHtml}
                    <a href="/api/files/${file.id}/download" class="btn btn-sm btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </div>`;
    }

    function showLoading(element) {
        element.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    }

    function loadMyFiles() {
        showLoading(myFilesList);
        $.get('/api/user/files', function(files) {
            myFilesList.empty();
            if (files.length > 0) {
                files.forEach(file => myFilesList.append(renderMyFileItem(file)));
            } else {
                myFilesList.html('<div class="p-4 text-center text-muted">You have not uploaded any files yet.</div>');
            }
        }).fail(() => {
            myFilesList.html('<div class="p-4 text-center text-danger">Could not load your files.</div>');
        });
    }

    function loadTeamFiles(teamId) {
        if (!teamId) {
            teamFilesList.html('<div class="p-4 text-center text-muted">Please select a team to view its files.</div>');
            return;
        }
        showLoading(teamFilesList);
        $.get(`/api/teams/${teamId}/files`, function(files) {
            teamFilesList.empty();
            if (files.length > 0) {
                files.forEach(file => teamFilesList.append(renderTeamFileItem(file)));
            } else {
                teamFilesList.html('<div class="p-4 text-center text-muted">No files have been shared with this team yet.</div>');
            }
        }).fail(() => {
            teamFilesList.html('<div class="p-4 text-center text-danger">Could not load team files.</div>');
        });
    }

    function loadUserTeams() {
        return $.get('/api/user/teams', function(response) {
            userTeams = response.teams;
            teamSelectFilter.empty().append('<option value="">-- Select a Team --</option>');
            userTeams.forEach(team => {
                teamSelectFilter.append(`<option value="${team.id}">${$('<div>').text(team.name).html()}</option>`);
            });
            if (response.current_team_id) {
                teamSelectFilter.val(response.current_team_id);
                loadTeamFiles(response.current_team_id);
            } else {
                loadTeamFiles(null);
            }
        });
    }

    // File Upload
    $('#submitUploadBtn').on('click', function() {
        const form = $('#uploadFileForm')[0];
        const formData = new FormData(form);
        const fileInput = $('#fileInput')[0];
        const button = $(this);

        if (fileInput.files.length === 0) {
            alert('Please select a file.');
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
        $('#upload-progress-bar').show().find('.progress-bar').css('width', '0%').text('0%');
        $('#upload-error').hide();

        $.ajax({
            url: '/api/files',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $('#upload-progress-bar .progress-bar').css('width', percentComplete + '%').text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function() {
                uploadFileModal.hide();
                loadMyFiles();
                const teamIdForUpload = $('#upload-team-id').val();
                const currentlySelectedTeamId = teamSelectFilter.val();
                if (teamIdForUpload && teamIdForUpload === currentlySelectedTeamId) {
                    loadTeamFiles(currentlySelectedTeamId);
                }
            },
            error: function(jqXHR) {
                const response = jqXHR.responseJSON;
                const message = response.message || 'An unknown error occurred.';
                $('#upload-error').text(message).show();
            },
            complete: function() {
                button.prop('disabled', false).html('Upload');
                $('#uploadFileForm')[0].reset();
                setTimeout(() => $('#upload-progress-bar').hide(), 1000);
            }
        });
    });

    // Open Share Modal
    myFilesList.on('click', '.share-btn', function() {
        const fileItem = $(this).closest('.file-list-item');
        const fileId = fileItem.data('file-id');
        const fileName = fileItem.find('strong').text();

        $('#share-file-id').val(fileId);
        $('#share-file-name').text(fileName);
        $('#share-error').hide();

        const teamListContainer = $('#share-team-list');
        teamListContainer.html('<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>');

        $.get('/api/user/files').done(function(files) {
            const currentFile = files.find(f => f.id === fileId);
            const sharedTeamIds = currentFile ? currentFile.shared_with_teams.map(t => t.id) : [];

            teamListContainer.empty();
            if (userTeams.length > 0) {
                userTeams.forEach(team => {
                    const isChecked = sharedTeamIds.includes(team.id);
                    teamListContainer.append(`
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${team.id}" id="team-share-${team.id}" name="team_ids[]" ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label" for="team-share-${team.id}">
                                ${$('<div>').text(team.name).html()}
                            </label>
                        </div>
                    `);
                });
            } else {
                teamListContainer.html('<p class="text-muted">You are not a member of any teams to share with.</p>');
            }
            shareFileModal.show();
        });
    });

    // Submit Share
    $('#submitShareBtn').on('click', function() {
        const button = $(this);
        const fileId = $('#share-file-id').val();
        const teamIds = $('#shareFileForm input[name="team_ids[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        $('#share-error').hide();

        $.ajax({
            url: `/api/files/${fileId}/share`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                team_ids: teamIds
            }),
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function() {
                shareFileModal.hide();
                loadMyFiles();
            },
            error: function(jqXHR) {
                const message = jqXHR.responseJSON.message || 'Could not update sharing settings.';
                $('#share-error').text(message).show();
            },
            complete: function() {
                button.prop('disabled', false).html('Save Sharing Settings');
            }
        });
    });

    // Revoke Share
    myFilesList.on('click', '.revoke-share-btn', function(e) {
        e.stopPropagation();
        const button = $(this);
        const fileId = button.closest('.file-list-item').data('file-id');
        const teamId = button.data('team-id');
        const teamName = button.data('team-name');

        if (!confirm(`Are you sure you want to stop sharing this file with the team "${teamName}"?`)) {
            return;
        }

        $.ajax({
            url: `/api/files/${fileId}/teams/${teamId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function() {
                loadMyFiles();
            },
            error: function(jqXHR) {
                alert('Error: ' + (jqXHR.responseJSON.message || 'Could not revoke access.'));
            }
        });
    });

    // Event Listeners
    teamSelectFilter.on('change', function() {
        loadTeamFiles($(this).val());
    });

    $('#uploadFileModal').on('show.bs.modal', function(event) {
        const myFilesTab = $('#my-files-tab');
        const uploadDestination = $('#upload-destination');
        const uploadTeamIdInput = $('#upload-team-id');

        if (myFilesTab.hasClass('active')) {
            uploadDestination.text('Your Files');
            uploadTeamIdInput.val('');
        } else {
            const selectedTeamId = teamSelectFilter.val();
            const selectedTeamName = teamSelectFilter.find('option:selected').text();
            if (selectedTeamId) {
                uploadDestination.html(`Team: <strong>${$('<div>').text(selectedTeamName).html()}</strong>`);
                uploadTeamIdInput.val(selectedTeamId);
            } else {
                // No team selected, default to personal but show a message
                uploadDestination.html('Your Files <small class="text-warning">(Select a team to upload directly to it)</small>');
                uploadTeamIdInput.val('');
            }
        }
    });


    // Initial Load
    loadMyFiles();
    loadUserTeams();
});
