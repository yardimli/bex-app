// public/js/files.js:

$(document).ready(function() {
    // MODIFIED: Selectors for DaisyUI modals (dialog elements)
    const uploadFileModal = document.getElementById('uploadFileModal');
    const shareFileModal = document.getElementById('shareFileModal');
    const myFilesList = $('#my-files-list');
    const teamFilesList = $('#team-files-list');
    const teamSelectFilter = $('#team-select-filter');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let userTeams = [];
    
    function getFileIcon(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-base-content/50'; // MODIFIED: text-muted -> text-base-content/50
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-error'; // MODIFIED: text-danger -> text-error
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-primary';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-info';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-secondary';
        return 'bi-file-earmark-fill text-base-content/50'; // MODIFIED: text-muted -> text-base-content/50
    }
    
    function formatBytes(bytes, decimals = 2) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    // MODIFIED: renderMyFileItem updated with Tailwind/DaisyUI classes
    function renderMyFileItem(file) {
        let sharedWithHtml = '<span class="text-base-content/70">Private</span>';
        if (file.shared_with_teams.length > 0) {
            sharedWithHtml = file.shared_with_teams.map(team => `
                <div class="badge badge-secondary gap-2">
                    ${$('<div>').text(team.name).html()}
                    <button class="revoke-share-btn" data-team-id="${team.id}" data-team-name="${$('<div>').text(team.name).html()}" aria-label="Revoke share">
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>
                </div>
            `).join(' ');
        }
        
        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            previewButtonHtml = `<button class="btn btn-xs btn-outline btn-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }
        
        return `
            <div class="flex items-center justify-between p-3 border-b border-base-300 gap-4" data-file-id="${file.id}">
                <div class="flex items-center gap-4 flex-grow min-w-0">
                    <i class="bi ${getFileIcon(file.mime_type)} text-3xl"></i>
                    <div class="flex-grow min-w-0">
                        <strong class="block truncate font-semibold">${$('<div>').text(file.original_filename).html()}</strong>
                        <div class="text-xs text-base-content/70">${formatBytes(file.size)} • Uploaded ${new Date(file.created_at).toLocaleDateString()}</div>
                        <div class="sharing-status mt-1 flex flex-wrap gap-1">${sharedWithHtml}</div>
                    </div>
                </div>
                <div class="file-actions flex-shrink-0 flex items-center gap-2">
                    ${previewButtonHtml}
                    <button class="btn btn-xs btn-outline btn-primary share-btn" title="Share"><i class="bi bi-share-fill"></i></button>
                    <a href="/api/files/${file.id}/download" class="btn btn-xs btn-outline btn-secondary" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </div>`;
    }
    
    // MODIFIED: renderTeamFileItem updated with Tailwind/DaisyUI classes
    function renderTeamFileItem(file) {
        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            previewButtonHtml = `<button class="btn btn-xs btn-outline btn-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }
        return `
            <div class="flex items-center justify-between p-3 border-b border-base-300 gap-4" data-file-id="${file.id}">
                <div class="flex items-center gap-4 flex-grow min-w-0">
                    <i class="bi ${getFileIcon(file.mime_type)} text-3xl"></i>
                    <div class="flex-grow min-w-0">
                        <strong class="block truncate font-semibold">${$('<div>').text(file.original_filename).html()}</strong>
                        <div class="text-xs text-base-content/70">
                            ${formatBytes(file.size)} • Shared by ${$('<div>').text(file.owner.name).html()} on ${new Date(file.pivot.shared_at).toLocaleDateString()}
                        </div>
                    </div>
                </div>
                <div class="file-actions flex-shrink-0 flex items-center gap-2">
                    ${previewButtonHtml}
                    <a href="/api/files/${file.id}/download" class="btn btn-xs btn-outline btn-secondary" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </div>`;
    }
    
    function showLoading(element) {
        element.html('<div class="text-center p-5"><span class="loading loading-spinner loading-lg text-primary"></span><p>Loading...</p></div>');
    }
    
    function loadMyFiles() {
        showLoading(myFilesList);
        $.get('/api/user/files', function(files) {
            myFilesList.empty();
            if (files.length > 0) {
                files.forEach(file => myFilesList.append(renderMyFileItem(file)));
            } else {
                myFilesList.html('<div class="p-4 text-center text-base-content/70">You have not uploaded any files yet.</div>');
            }
        }).fail(() => {
            myFilesList.html('<div class="p-4 text-center text-error">Could not load your files.</div>');
        });
    }
    
    function loadTeamFiles(teamId) {
        if (!teamId) {
            teamFilesList.html('<div class="p-4 text-center text-base-content/70">Please select a team to view its files.</div>');
            return;
        }
        showLoading(teamFilesList);
        $.get(`/api/teams/${teamId}/files`, function(files) {
            teamFilesList.empty();
            if (files.length > 0) {
                files.forEach(file => teamFilesList.append(renderTeamFileItem(file)));
            } else {
                teamFilesList.html('<div class="p-4 text-center text-base-content/70">No files have been shared with this team yet.</div>');
            }
        }).fail(() => {
            teamFilesList.html('<div class="p-4 text-center text-error">Could not load team files.</div>');
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
        
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span> Uploading...');
        $('#upload-progress-container').show(); // MODIFIED: Selector for progress container
        $('#upload-progress-bar').css('width', '0%').text('0%');
        $('#upload-error').hide();
        
        $.ajax({
            url: '/api/files',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $('#upload-progress-bar').css('width', percentComplete + '%').text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function() {
                uploadFileModal.close(); // MODIFIED: Use .close() for <dialog>
                loadMyFiles();
                const teamIdForUpload = $('#upload-team-id').val();
                const currentlySelectedTeamId = teamSelectFilter.val();
                if (teamIdForUpload && teamIdForUpload === currentlySelectedTeamId) {
                    loadTeamFiles(currentlySelectedTeamId);
                }
            },
            error: function(jqXHR) {
                const message = jqXHR.responseJSON?.message || 'An unknown error occurred.';
                $('#upload-error').text(message).show();
            },
            complete: function() {
                button.prop('disabled', false).html('Upload');
                $('#uploadFileForm')[0].reset();
                setTimeout(() => $('#upload-progress-container').hide(), 1000);
            }
        });
    });
    
    // Open Share Modal
    myFilesList.on('click', '.share-btn', function() {
        const fileItem = $(this).closest('[data-file-id]'); // MODIFIED: More robust selector
        const fileId = fileItem.data('file-id');
        const fileName = fileItem.find('strong').text();
        
        $('#share-file-id').val(fileId);
        $('#share-file-name').text(fileName);
        $('#share-error').hide();
        
        const teamListContainer = $('#share-team-list');
        teamListContainer.html('<div class="text-center"><span class="loading loading-spinner"></span></div>');
        
        $.get('/api/user/files').done(function(files) {
            const currentFile = files.find(f => f.id === fileId);
            const sharedTeamIds = currentFile ? currentFile.shared_with_teams.map(t => t.id) : [];
            
            teamListContainer.empty();
            if (userTeams.length > 0) {
                userTeams.forEach(team => {
                    const isChecked = sharedTeamIds.includes(team.id);
                    // MODIFIED: DaisyUI form-control and checkbox
                    teamListContainer.append(`
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text">${$('<div>').text(team.name).html()}</span>
                                <input type="checkbox" value="${team.id}" name="team_ids[]" class="checkbox checkbox-primary" ${isChecked ? 'checked' : ''} />
                            </label>
                        </div>
                    `);
                });
            } else {
                teamListContainer.html('<p class="text-base-content/70">You are not a member of any teams to share with.</p>');
            }
            shareFileModal.showModal(); // MODIFIED: Use .showModal() for <dialog>
        });
    });
    
    // Submit Share
    $('#submitShareBtn').on('click', function() {
        const button = $(this);
        const fileId = $('#share-file-id').val();
        const teamIds = $('#shareFileForm input[name="team_ids[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span> Saving...');
        $('#share-error').hide();
        
        $.ajax({
            url: `/api/files/${fileId}/share`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ team_ids: teamIds }),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                shareFileModal.close(); // MODIFIED: Use .close() for <dialog>
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
        const fileId = button.closest('[data-file-id]').data('file-id');
        const teamId = button.data('team-id');
        const teamName = button.data('team-name');
        
        if (!confirm(`Are you sure you want to stop sharing this file with the team "${teamName}"?`)) {
            return;
        }
        
        $.ajax({
            url: `/api/files/${fileId}/teams/${teamId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
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
    
    // MODIFIED: Replaced 'show.bs.modal' event with a click handler on the trigger button.
    $('#openUploadModalBtn').on('click', function() {
        const uploadDestination = $('#upload-destination');
        const uploadTeamIdInput = $('#upload-team-id');
        
        // Check which tab is active by looking at the checked radio input
        if ($('#my-files-tab-radio').is(':checked')) {
            uploadDestination.text('Your Files');
            uploadTeamIdInput.val('');
        } else {
            const selectedTeamId = teamSelectFilter.val();
            const selectedTeamName = teamSelectFilter.find('option:selected').text();
            if (selectedTeamId) {
                uploadDestination.html(`Team: <strong>${$('<div>').text(selectedTeamName).html()}</strong>`);
                uploadTeamIdInput.val(selectedTeamId);
            } else {
                uploadDestination.html('Your Files <small class="text-warning">(Select a team to upload directly to it)</small>');
                uploadTeamIdInput.val('');
            }
        }
        uploadFileModal.showModal(); // Show the modal after setting it up
    });
    
    // Initial Load
    loadMyFiles();
    loadUserTeams();
});
