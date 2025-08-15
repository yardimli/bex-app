// public/js/team-files.js:

$(document).ready(function() {
    // MODIFIED: Get dialog element
    const teamFilesModal = document.getElementById('teamFilesModal');
    if (!teamFilesModal) {
        return; // Don't run if the modal isn't on the page
    }

    const teamFilesList = $('#team-files-modal-list');
    const teamNameDisplay = $('#team-files-modal-team-name');
    const detailsPane = $('#team-files-modal-details-pane');
    const filterLinks = $('#team-files-modal-filters a'); // MODIFIED: Target 'a' tags inside
    const searchInput = $('#team-files-modal-search');
    let debounceTimer;

    function getFileIcon(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-base-content/50';
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-red-500';
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-blue-500';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-cyan-500';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-gray-500';
        return 'bi-file-earmark-fill text-base-content/50';
    }

    function renderTeamFileItem(file) {
        let previewButtonHtml = '';
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            // MODIFIED: Use DaisyUI button classes
            previewButtonHtml = `<button class="btn btn-sm btn-outline btn-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }

        const favoriteIconClass = file.is_favorited ? 'bi-heart-fill text-error' : 'bi-heart';
        const favoriteButtonHtml = `
            <button class="btn btn-sm btn-ghost favorite-btn" title="Favorite" data-file-id="${file.id}">
                <i class="bi ${favoriteIconClass}"></i>
            </button>`;

        // MODIFIED: Replaced Bootstrap structure with DaisyUI/Tailwind
        return `
            <a href="#" class="flex items-center justify-between p-3 bg-base-100 hover:bg-base-200 rounded-lg file-item" data-id="${file.id}">
                <div class="flex items-center min-w-0 gap-4">
                    <i class="bi ${getFileIcon(file.mime_type)} text-3xl"></i>
                    <div class="text-truncate">
                        <strong class="block text-truncate">${$('<div>').text(file.original_filename).html()}</strong>
                        <small class="text-base-content/60">Shared by ${$('<div>').text(file.owner.name).html()} on ${new Date(file.pivot.shared_at).toLocaleDateString()}</small>
                    </div>
                </div>
                <div class="flex items-center flex-shrink-0 gap-2">
                     ${favoriteButtonHtml}
                    ${previewButtonHtml}
                    <a href="/api/files/${file.id}/download" class="btn btn-sm btn-outline" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </a>`;
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

    // MODIFIED: Changed from Bootstrap event to a click handler on the trigger button
    $('#teamWorkspaceButton').on('click', function() {
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const teamLink = $(`#account-switcher-submenu a[data-team-id="${currentTeamId}"]`);
        const teamName = teamLink.length ? teamLink.find('span').first().text() : 'Your Team';

        if (currentTeamId && currentTeamId !== '0') {
            teamNameDisplay.text(teamName);
            searchInput.val('');
            filterLinks.filter('[data-filter="recent"]').trigger('click');
        } else {
            teamNameDisplay.text('No Team Selected');
            teamFilesList.html('<div class="text-center text-base-content/70 p-4">Please switch to a team to view its workspace.</div>');
            detailsPane.html('');
            filterLinks.removeClass('active');
        }
        // MODIFIED: Use native showModal()
        teamFilesModal.showModal();
    });

    filterLinks.on('click', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        filterLinks.removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('filter');
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');

        if (currentTeamId && currentTeamId !== '0') {
            loadTeamFiles(currentTeamId, searchInput.val(), filter);
        }
    });

    searchInput.on('keyup', function() {
        clearTimeout(debounceTimer);
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const searchValue = $(this).val();
        const activeFilter = filterLinks.filter('.active').data('filter') || 'recent';

        if (currentTeamId && currentTeamId !== '0') {
            debounceTimer = setTimeout(() => {
                loadTeamFiles(currentTeamId, searchValue, activeFilter);
            }, 400);
        }
    });

    teamFilesList.on('click', '.file-item', function(e) {
        e.preventDefault();
        if ($(e.target).closest('.btn').length) {
            return;
        }

        teamFilesList.find('.file-item').removeClass('bg-base-300'); // Use a visual indicator class
        $(this).addClass('bg-base-300');

        const fileName = $(this).find('strong').text().trim();
        detailsPane.html(`
            <h5 class="font-bold text-truncate">${$('<div>').text(fileName).html()}</h5>
            <p class="text-base-content/70 text-sm mt-2">Use the <i class="bi bi-eye-fill"></i> Preview or <i class="bi bi-download"></i> Download buttons to access the file.</p>
        `);
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
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
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
});
