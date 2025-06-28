$(document).ready(function() {
    const teamFilesModal = $('#teamFilesModal');
    if (!teamFilesModal.length) {
        return; // Don't run if the modal isn't on the page
    }

    const teamFilesList = $('#team-files-modal-list');
    const teamNameDisplay = $('#team-files-modal-team-name');
    const detailsPane = $('#team-files-modal-details-pane');
    const filterLinks = $('#team-files-modal-filters .list-group-item');
    const searchInput = $('#team-files-modal-search');
    let debounceTimer;

    // Helper function to get a file-type-specific icon
    function getFileIcon(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-muted';
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-danger';
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-primary';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-info';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-secondary';
        return 'bi-file-earmark-fill text-muted';
    }

    // Renders a single file item for the list
    function renderTeamFileItem(file) {
        let previewButtonHtml = '';
        // The global click handler in app.js will handle the preview action
        if (file.mime_type.startsWith('image/') || file.mime_type === 'application/pdf') {
            previewButtonHtml = `<button class="btn btn-sm btn-outline-info preview-btn" title="Preview" data-file-id="${file.id}" data-mime-type="${file.mime_type}"><i class="bi bi-eye-fill"></i></button>`;
        }

        return `<div class="d-flex border rounded p-2">
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-id="${file.id}">
                <div class="d-flex align-items-center" style="min-width: 0;">
                    <i class="bi ${getFileIcon(file.mime_type)} me-3 fs-4"></i>
                    <div class="text-truncate">
                        <strong class="d-block text-truncate">${$('<div>').text(file.original_filename).html()}</strong>
                        <small class="text-muted">Shared by ${$('<div>').text(file.owner.name).html()} on ${new Date(file.pivot.shared_at).toLocaleDateString()}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center flex-shrink-0">
                    ${previewButtonHtml}
                    <a href="/api/files/${file.id}/download" class="btn btn-sm btn-outline-secondary ms-2" title="Download"><i class="bi bi-download"></i></a>
                </div>
            </a></div>`;
    }


    function loadTeamFiles(teamId, searchTerm = '') {
        teamFilesList.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        detailsPane.html('Select a file to view details.');

        $.get(`/api/teams/${teamId}/files`, { search: searchTerm })
            .done(function(files) {
                teamFilesList.empty();
                if (files.length > 0) {
                    files.forEach(file => teamFilesList.append(renderTeamFileItem(file)));
                } else {
                    if (searchTerm) {
                        teamFilesList.html(`<div class="list-group-item text-center text-muted p-4">No files found matching "<strong>${$('<div>').text(searchTerm).html()}</strong>".</div>`);
                    } else {
                        teamFilesList.html('<div class="list-group-item text-center text-muted p-4">No files have been shared with this team yet.</div>');
                    }
                }
            })
            .fail(function() {
                teamFilesList.html('<div class="list-group-item text-center text-danger p-4">Could not load team files. Please try again later.</div>');
            });
    }

    teamFilesModal.on('show.bs.modal', function() {
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');

        // Find the team name from the account switcher dropdown in the header
        const teamLink = $(`#account-switcher-submenu a[data-team-id="${currentTeamId}"]`);
        const teamName = teamLink.length ? teamLink.find('span').first().text() : 'Your Team';

        if (currentTeamId && currentTeamId !== '0') {
            teamNameDisplay.text(teamName);
            searchInput.val('');
            filterLinks.filter('[data-filter="recent"]').trigger('click');
        } else {
            teamNameDisplay.text('No Team Selected');
            teamFilesList.html('<div class="list-group-item text-center text-muted p-4">Please switch to a team to view its workspace.</div>');
            detailsPane.html('');
            filterLinks.removeClass('active');
        }
    });

    // Handle clicks on the filter links ("All Files", "Recent")
    filterLinks.on('click', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        filterLinks.removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('filter');
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');

        if (currentTeamId && currentTeamId !== '0') {
            // For now, both 'all' and 'recent' load the same default list.
            if (filter === 'all' || filter === 'recent') {
                loadTeamFiles(currentTeamId, searchInput.val());
            }
        }
    });

    searchInput.on('keyup', function() {
        clearTimeout(debounceTimer); // Reset the timer
        const currentTeamId = $('meta[name="current-team-id"]').attr('content');
        const searchValue = $(this).val();

        if (currentTeamId && currentTeamId !== '0') {
            // Wait 400ms after the user stops typing to make the API call
            debounceTimer = setTimeout(() => {
                loadTeamFiles(currentTeamId, searchValue);
            }, 400);
        }
    });

    // Handle clicks on file items to show basic info in the details pane
    teamFilesList.on('click', '.list-group-item', function(e) {
        e.preventDefault();
        // Don't trigger for buttons inside the item
        if ($(e.target).closest('.btn').length) {
            return;
        }

        teamFilesList.find('.list-group-item').removeClass('active');
        $(this).addClass('active');

        const fileName = $(this).find('strong').text().trim();
        detailsPane.html(`
            <div class="p-3">
                <h5 class="text-truncate">${$('<div>').text(fileName).html()}</h5>
                <p class="text-muted small">Use the <i class="bi bi-eye-fill"></i> Preview or <i class="bi bi-download"></i> Download buttons to access the file.</p>
            </div>
        `);
    });
});
