// public/js/team-files.js:

$(document).ready(function() {
    // Get dialog element
    const teamFilesModal = document.getElementById('teamFilesModal');
    if (!teamFilesModal) {
        return; // Don't run if the modal isn't on the page
    }

    const teamFilesList = $('#team-files-modal-list');
    const teamNameDisplay = $('#team-files-modal-team-name');
    const teamAvatarImg = $('#team-files-modal-avatar');
    const detailsPane = $('#team-files-modal-details-pane');
    const filterLinks = $('#team-files-modal-filters a'); // Target 'a' tags inside
    const searchInput = $('#team-files-modal-search');
    let debounceTimer;
    let userTeamsData = []; //To cache all team data
    let userData = {};

    // Function to fetch and cache team data on load
    function loadUserTeams() {
        $.get('/api/user/teams', function(response) {
            if (response.teams) {
                userTeamsData = response.teams;
            }
            // ADDED: Cache user data
            if (response.user_name) {
                userData = {
                    name: response.user_name,
                    avatar_url: response.user_avatar_url
                };
            }
        }).fail(function() {
            console.error("Could not load user teams data for the workspace modal.");
        });
    }

    function getFileIcon(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-base-content/50';
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-red-500';
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-blue-500';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-cyan-500';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-gray-500';
        return 'bi-file-earmark-fill text-base-content/50';
    }

    // MODIFIED: renderTeamFileItem to handle both team and personal files
    function renderTeamFileItem(file) {
        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            // MODIFIED: Use DaisyUI button classes
            previewButtonHtml = `<button class="btn btn-sm btn-outline btn-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }
        const favoriteIconClass = file.is_favorited ? 'bi-heart-fill text-error' : 'bi-heart';
        const favoriteButtonHtml = ` <button class="btn btn-sm btn-ghost favorite-btn" title="Favorite" data-file-id="${file.id}"> <i class="bi ${favoriteIconClass}"></i> </button>`;

        // Logic for Summarize button
        const allowedSummarizeTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        const isSummarizable = allowedSummarizeTypes.includes(file.mime_type) || file.original_filename.toLowerCase().endsWith('.docx');
        let summarizeButtonHtml = '';
        if (isSummarizable) {
            summarizeButtonHtml = `<button class="btn btn-sm btn-outline btn-accent summarize-btn ml-1" title="Summarize this document" data-file-id="${file.id}">Summarize</button>`;
        }

        // Conditional logic for shared/uploaded info
        const sharedInfo = file.pivot
            ? `Shared by ${$('<div>').text(file.owner.name).html()} on ${new Date(file.pivot.shared_at).toLocaleDateString()}`
            : `Uploaded on ${new Date(file.created_at).toLocaleDateString()}`;

        // Replaced Bootstrap structure with DaisyUI/Tailwind and used sharedInfo
        return ` <a href="#" class="flex items-center justify-between p-3 bg-base-100 hover:bg-base-200 rounded-lg file-item" data-id="${file.id}"> <div class="flex items-center min-w-0 gap-4"> <i class="bi ${getFileIcon(file.mime_type)} text-3xl"></i> <div class="text-truncate"> <strong class="block text-truncate">${$('<div>').text(file.original_filename).html()}</strong> <small class="text-base-content/60">${sharedInfo}</small> </div> </div> <div class="flex items-center flex-shrink-0 gap-2"> ${favoriteButtonHtml} ${previewButtonHtml} ${summarizeButtonHtml} <a href="/api/files/${file.id}/download" class="btn btn-sm btn-outline" title="Download"><i class="bi bi-download"></i></a> </div> </a>`;
    }

    function loadTeamFiles(teamId, searchTerm = '', filterType = 'recent') {
        // MODIFIED: Use DaisyUI spinner
        teamFilesList.html('<div class="flex justify-center items-center h-full"><span class="loading loading-spinner loading-lg"></span></div>');
        detailsPane.html('Select a file to view details.');

        $.get(`/api/teams/${teamId}/files`, { search: searchTerm, filter: filterType })
            .done(function(files) {
                teamFilesList.empty();
                if (files.length > 0) {
                    files.forEach(file => teamFilesList.append(renderTeamFileItem(file)));
                } else {
                    if (searchTerm) {
                        teamFilesList.html(`<div class="text-center text-base-content/70 p-4">No files found matching "<strong>${$('<div>').text(searchTerm).html()}</strong>".</div>`);
                    } else if (filterType === 'favorites') {
                        teamFilesList.html('<div class="text-center text-base-content/70 p-4">You have not favorited any files in this team yet.</div>');
                    } else {
                        teamFilesList.html('<div class="text-center text-base-content/70 p-4">No files have been shared with this team yet.</div>');
                    }
                }
            })
            .fail(function() {
                teamFilesList.html('<div class="text-center text-error p-4">Could not load team files. Please try again later.</div>');
            });
    }

    // Rewrote the click handler to use cached team data
    function loadWorkspaceFiles(contextId, searchTerm = '', filterType = 'recent') {
        // MODIFIED: Use DaisyUI spinner
        teamFilesList.html('<div class="flex justify-center items-center h-full"><span class="loading loading-spinner loading-lg"></span></div>');
        detailsPane.html('Select a file to view details.');

        // ADDED: Determine API endpoint and data based on context
        const isPersonal = contextId === 'personal';
        const apiUrl = isPersonal ? '/api/user/files' : `/api/teams/${contextId}/files`;
        // NOTE: The personal files endpoint doesn't support search/filter, so we only send that data for teams.
        const requestData = isPersonal ? {} : { search: searchTerm, filter: filterType };

        $.get(apiUrl, requestData)
            .done(function(files) {
                teamFilesList.empty();
                if (files.length > 0) {
                    files.forEach(file => teamFilesList.append(renderTeamFileItem(file)));
                } else {
                    if (searchTerm) {
                        teamFilesList.html(`<div class="text-center text-base-content/70 p-4">No files found matching "<strong>${$('<div>').text(searchTerm).html()}</strong>".</div>`);
                    } else if (filterType === 'favorites' && !isPersonal) {
                        teamFilesList.html('<div class="text-center text-base-content/70 p-4">You have not favorited any files in this team yet.</div>');
                    } else if (isPersonal) {
                        teamFilesList.html('<div class="text-center text-base-content/70 p-4">You have not uploaded any files yet.</div>');
                    } else {
                        teamFilesList.html('<div class="text-center text-base-content/70 p-4">No files have been shared with this team yet.</div>');
                    }
                }
            })
            .fail(function() {
                teamFilesList.html('<div class="text-center text-error p-4">Could not load files. Please try again later.</div>');
            });
    }

    // MODIFIED: Rewrote the click handler to use cached data and support both contexts
    $('#teamWorkspaceButton').on('click', function() {
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const isPersonalContext = !currentTeamId || currentTeamId === '0';
        const modalTitle = $('#team-files-modal-title');

        // Reset to a loading state immediately
        teamNameDisplay.text('Loading...');
        teamAvatarImg.attr('src', 'https://ui-avatars.com/api/?name=?');
        teamAvatarImg.attr('alt', 'Loading Avatar');
        teamFilesList.html('<div class="flex justify-center items-center h-full" id="team-files-modal-loader"><span class="loading loading-spinner loading-lg"></span></div>');
        detailsPane.html('Select a file to view details.');
        teamFilesModal.showModal();

        searchInput.val('');
        filterLinks.removeClass('active');
        filterLinks.filter('[data-filter="recent"]').addClass('active');

        if (isPersonalContext) {
            // PERSONAL CONTEXT
            modalTitle.text('My Workspace');
            if (userData.name) {
                teamNameDisplay.text(userData.name);
                teamAvatarImg.attr('src', userData.avatar_url);
                teamAvatarImg.attr('alt', userData.name + "'s Avatar");
                loadWorkspaceFiles('personal', '', 'recent');
            } else {
                teamNameDisplay.text('Error loading user data');
            }
        } else {
            // TEAM CONTEXT
            modalTitle.text('Team Workspace');
            const currentTeam = userTeamsData.find(team => team.id == currentTeamId);
            if (currentTeam) {
                teamNameDisplay.text(currentTeam.name);
                teamAvatarImg.attr('src', currentTeam.avatar_url);
                teamAvatarImg.attr('alt', currentTeam.name + "'s Avatar");
                loadWorkspaceFiles(currentTeamId, '', 'recent');
            } else {
                teamNameDisplay.text('Error: Team not found');
            }
        }
    });

    // MODIFIED: Filter and Search handlers to check context
    filterLinks.on('click', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;
        filterLinks.removeClass('active');
        $(this).addClass('active');
        const filter = $(this).data('filter');
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const contextId = (!currentTeamId || currentTeamId === '0') ? 'personal' : currentTeamId;
        loadWorkspaceFiles(contextId, searchInput.val(), filter);
    });

    searchInput.on('keyup', function() {
        clearTimeout(debounceTimer);
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const contextId = (!currentTeamId || currentTeamId === '0') ? 'personal' : currentTeamId;
        const searchValue = $(this).val();
        const activeFilter = filterLinks.filter('.active').data('filter') || 'recent';
        debounceTimer = setTimeout(() => {
            loadWorkspaceFiles(contextId, searchValue, activeFilter);
        }, 400);
    });

    teamFilesList.on('click', '.file-item', function(e) {
        e.preventDefault();
        if ($(e.target).closest('.btn').length) {
            return;
        }
        teamFilesList.find('.file-item').removeClass('bg-base-300'); // Use a visual indicator class
        $(this).addClass('bg-base-300');
        const fileName = $(this).find('strong').text().trim();
        detailsPane.html(` <h5 class="font-bold text-truncate">${$('<div>').text(fileName).html()}</h5> <p class="text-base-content/70 text-sm mt-2">Use the <i class="bi bi-eye-fill"></i> Preview or <i class="bi bi-download"></i> Download buttons to access the file.</p> `);
    });

    // ADDED: Click handler for the new Summarize button
    teamFilesList.on('click', '.summarize-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const button = $(this);
        const fileId = button.data('file-id');
        const originalIcon = button.html();

        button.prop('disabled', true).html('<span class="loading loading-spinner loading-xs"></span>');

        $.ajax({
            url: `/api/summarize/file-id`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                file_id: fileId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.context_key) {
                    teamFilesModal.close(); // Close the workspace modal
                    window.location.href = `/chat?summarize_key=${response.context_key}`;
                } else {
                    alert('Error: ' + (response.error || 'Could not prepare file for summarization.'));
                    button.prop('disabled', false).html(originalIcon);
                }
            },
            error: function(jqXHR) {
                const errorMsg = jqXHR.responseJSON?.error || 'An unknown error occurred.';
                alert('Error: ' + errorMsg);
                button.prop('disabled', false).html(originalIcon);
            }
        });
    });

    teamFilesList.on('click', '.favorite-btn', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the file details from showing
        const button = $(this);
        const fileId = button.data('file-id');
        const icon = button.find('i');
        // Optimistically update UI
        icon.toggleClass('bi-heart bi-heart-fill text-error');
        $.ajax({
            url: `/api/files/${fileId}/toggle-favorite`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                // Correct the UI based on the actual server response
                if (response.is_favorited) {
                    icon.removeClass('bi-heart').addClass('bi-heart-fill text-error');
                } else {
                    icon.removeClass('bi-heart-fill text-error').addClass('bi-heart');
                }
            },
            error: function() {
                // Revert on error
                icon.toggleClass('bi-heart bi-heart-fill text-error');
                alert('Could not update favorite status. Please try again.');
            }
        });
    });

    // Initial Load
    loadUserTeams();
});
