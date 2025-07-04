window.BexApp = {
    attachedFiles: new Map(),

    getFileIcon: function(mimeType) {
        if (!mimeType) return 'bi-file-earmark-fill text-muted';
        if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-danger';
        if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-primary';
        if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-info';
        if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-secondary';
        return 'bi-file-earmark-fill text-muted';
    },

    renderFilePills: function() {
        const container = $('#file-pills-container');
        if (!container.length) return;

        container.empty();
        const fileIds = [];
        this.attachedFiles.forEach((file, id) => {
            container.append(`
                <span class="badge text-bg-secondary d-inline-flex align-items-center gap-2 p-2">
                    <i class="bi ${this.getFileIcon(file.mime_type)}"></i>
                    <span>${$('<div>').text(file.name).html()}</span>
                    <button type="button" class="btn-close btn-close-white ms-1 remove-file-pill" data-id="${id}" aria-label="Remove"></button>
                </span>
            `);
            fileIds.push(id);
        });
        $('#attached-files-input').val(fileIds.length > 0 ? JSON.stringify(fileIds) : '');
    }
};
$(document).ready(function () {
    const themeToggleButton = $('#themeToggleButton');
    const htmlElement = $('html');
    const currentTheme = localStorage.getItem('theme');
    const lightIcon = 'bi-brightness-high-fill';
    const darkIcon = 'bi-moon-stars-fill';

    // --- Sidebar Toggle Elements ---
    const sidebarToggle = $('#sidebarToggle');
    const body = $('body');
    const sidebarBackdrop = $('.sidebar-backdrop');
    const breakpoint = 991.98; // Bootstrap LG breakpoint

    const modeDropdownButton = $('#modeDropdownButton');
    const modeDropdownMenu = modeDropdownButton.next('.dropdown-menu');
    const selectedModelNameSpan = $('#selected-model-name');
    const defaultModelId = 'openai/gpt-4o-mini';

    if (modeDropdownButton.length) {
        function applySelectedModel(modelId) {
            const selectedItem = modeDropdownMenu.find(`.dropdown-item[data-model-id="${modelId}"]`);
            let displayName = 'Smart Mode';
            modeDropdownMenu.find('.dropdown-item').removeClass('active').find('i.bi-check').remove();
            if (selectedItem.length) {
                displayName = selectedItem.data('display-name') || selectedItem.text().trim();
                selectedItem.addClass('active').prepend('<i class="bi bi-check me-2"></i>');
                console.log('Applied model:', modelId, 'Display:', displayName);
            } else {
                const defaultItem = modeDropdownMenu.find(`.dropdown-item[data-model-id="${defaultModelId}"]`);
                if (defaultItem.length) {
                    displayName = defaultItem.data('display-name') || defaultItem.text().trim();
                    defaultItem.addClass('active').prepend('<i class="bi bi-check me-2"></i>');
                    console.log('Applied default model (fallback):', defaultModelId, 'Display:', displayName);
                } else {
                    console.error("Default model item not found in dropdown!");
                }
            }
            if (selectedModelNameSpan.length) {
                selectedModelNameSpan.text(displayName);
            } else {
                modeDropdownButton.text(displayName);
            }
        }

        modeDropdownMenu.on('click', '.dropdown-item', function (e) {
            e.preventDefault();
            const selectedModelId = $(this).data('model-id');
            if (selectedModelId) {
                localStorage.setItem('selectedLlmModel', selectedModelId);
                applySelectedModel(selectedModelId);
                console.log('Model selection saved:', selectedModelId);
            }
        });

        const savedModel = localStorage.getItem('selectedLlmModel');
        applySelectedModel(savedModel || defaultModelId);
    }

    function isMobile() {
        return $(window).width() <= breakpoint;
    }

    function applySidebarState() {
        if (isMobile()) {
            body.removeClass('sidebar-collapsed');
            body.removeClass('sidebar-mobile-shown');
            sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list');
        } else {
            const desktopState = localStorage.getItem('sidebarState');
            if (desktopState === 'collapsed') {
                body.addClass('sidebar-collapsed');
                sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list');
            } else {
                body.removeClass('sidebar-collapsed');
                sidebarToggle.find('i').removeClass('bi-list').addClass('bi-x');
            }
            body.removeClass('sidebar-mobile-shown');
        }
    }

    sidebarToggle.on('click', function () {
        const icon = $(this).find('i');
        if (isMobile()) {
            body.toggleClass('sidebar-mobile-shown');
            if (body.hasClass('sidebar-mobile-shown')) {
                icon.removeClass('bi-list').addClass('bi-x');
            } else {
                icon.removeClass('bi-x').addClass('bi-list');
            }
        } else {
            body.toggleClass('sidebar-collapsed');
            if (body.hasClass('sidebar-collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
                icon.removeClass('bi-x').addClass('bi-list');
            } else {
                localStorage.setItem('sidebarState', 'expanded');
                icon.removeClass('bi-list').addClass('bi-x');
            }
        }
    });

    sidebarBackdrop.on('click', function () {
        if (isMobile() && body.hasClass('sidebar-mobile-shown')) {
            body.removeClass('sidebar-mobile-shown');
            sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list');
        }
    });

    let resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            applySidebarState();
        }, 250);
    });

    applySidebarState();

    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlElement.addClass('dark-mode');
            themeToggleButton.find('i').removeClass(lightIcon).addClass(darkIcon);
            localStorage.setItem('theme', 'dark');
        } else {
            htmlElement.removeClass('dark-mode');
            themeToggleButton.find('i').removeClass(darkIcon).addClass(lightIcon);
            localStorage.setItem('theme', 'light');
        }
    }

    applyTheme(currentTheme || 'light');

    $('#meetingSummaryButton').on('click', function () {
        $('#recentMeetingsModal').modal('show');
    });
    $('#actionItemsButton').on('click', function () {
        $('#actionItemsModal').modal('show');
    });
    $('#myNotesButton').on('click', function () {
        $('#myNotesModal').modal('show');
    });
    $('#myRecordingsButton').on('click', function () {
        $('#myRecordingsModal').modal('show');
    });
    $('#summarizeButton').on('click', function () {
        $('#summarizeContentModal').modal('show');
    });
    $('#transcribeButton').on('click', function () {
        $('#transcribeModal').modal('show');
    });
    $('#teamWorkspaceButton').on('click', function () {
        $('#teamFilesModal').modal('show');
    });
    $('#settingsButton').on('click', function () {
        $('#settingsModal').modal('show');
    });

    themeToggleButton.on('click', function (e) {
        e.preventDefault();
        const newTheme = htmlElement.hasClass('dark-mode') ? 'light' : 'dark';
        applyTheme(newTheme);
        console.log('Theme changed to:', newTheme);
    });

    // REMOVED: Placeholder function for handling modal item clicks.
    // The new team-files.js handles its own logic.

    $('#saveSettingsButton').on('click', function () {
        const selectedDefaultModel = $('#defaultModeSelect').val();
        const selectedThemeValue = $('#themeSelect').val().toLowerCase();
        const selectedToneValue = $('input[name="personalityTone"]:checked').val();

        console.log('Saving settings:', {
            defaultModel: selectedDefaultModel,
            theme: selectedThemeValue,
            tone: selectedToneValue
        });

        applyTheme(selectedThemeValue);
        localStorage.setItem('selectedPersonalityTone', selectedToneValue);
        localStorage.setItem('selectedLlmModel', selectedDefaultModel);
        console.log('Saved default model setting:', selectedDefaultModel);

        $('#settingsModal').modal('hide');
    });

    // --- Summarize Content Logic ---
    // Helper function to redirect to chat with a prompt
    function redirectToChatWithSummarizationData(data) {
        const chatUrl = '/chat';
        let redirectUrl;

        if (data.context_key) {
            // Pass the key and a preview (or a flag to fetch full text)
            // The chat page will use the key to get the full text from session
            redirectUrl = chatUrl + '?summarize_key=' + encodeURIComponent(data.context_key) + '&prompt_text=' + encodeURIComponent(data.prompt_text || '');
        } else if (data.full_text_for_prompt) {
            const promptText = (data.prompt_text || '') + data.full_text_for_prompt;
            redirectUrl = chatUrl + '?prompt=' + encodeURIComponent(promptText);
        } else {
            alert('Error: Could not prepare summarization data.');
            return;
        }

        $('#summarizeContentModal').modal('hide');
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 150);
    }

    function redirectToChatWithPrompt(promptText) {
        const chatUrl = '/chat'; // Base URL for new chats
        const redirectUrl = chatUrl + '?prompt=' + encodeURIComponent(promptText);
        $('#summarizeContentModal').modal('hide'); // Hide modal first
        // Small delay to ensure modal is hidden before navigation, preventing UI glitches
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 150);
    }

    $('#summarizeWebButton').on('click', function () {
        const url = $('#summarizeUrlInput').val().trim();
        if (!url) {
            alert('Please enter a URL.');
            return;
        }
        try {
            new URL(url); // Basic check for valid URL structure
        } catch (_) {
            alert('Please enter a valid URL.');
            return;
        }

        const button = $(this);
        const originalButtonText = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching & Processing URL...');

        $.ajax({
            url: '/api/summarize/url',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                url: url
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    redirectToChatWithSummarizationData({
                        context_key: response.context_key, // Will be undefined if not set
                        full_text_for_prompt: response.full_text_for_prompt, // Will be undefined if not set
                        prompt_text: response.prompt_text
                    });
                } else {
                    alert('Error: ' + (response.error || 'Could not process the URL.'));
                    button.prop('disabled', false).html(originalButtonText);
                }
            },
            error: function(jqXHR) {
                // ... (error handling remains similar)
                let errorMsg = 'An unknown error occurred while processing the URL.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    errorMsg = jqXHR.responseJSON.error;
                } else if (jqXHR.responseText) {
                    try {
                        const err = JSON.parse(jqXHR.responseText);
                        if (err.message) errorMsg = err.message;
                    } catch (e) {}
                }
                alert('Error: ' + errorMsg);
                button.prop('disabled', false).html(originalButtonText);
            }
        });
    });

    $('#summarizeTextButton').on('click', function () {
        const text = $('#summarizeTextInput').val().trim();
        if (!text) {
            alert('Please paste some text to summarize.');
            return;
        }
        // For direct text, we always use the 'prompt' method as it's user-entered
        const promptText = `Summarize the following text:\n\n${text}`;
        const chatUrl = '/chat';
        const redirectUrl = chatUrl + '?prompt=' + encodeURIComponent(promptText);
        $('#summarizeContentModal').modal('hide');
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 150);
    });

    $('#summarizeFileButton').on('click', function () {
        const fileInput = $('#summarizeFileInput');
        const file = fileInput.prop('files')[0];
        if (!file) {
            alert('Please select a file.');
            return;
        }

        // Basic client-side check, server will do more robust validation
        const allowedTypes = ['text/plain', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        const allowedExtensions = ['.txt', '.pdf', '.docx'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
            alert('Unsupported file type. Please upload a TXT, PDF, or DOCX file.');
            fileInput.val('');
            return;
        }

        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            alert('File is too large. Maximum size is 10MB.');
            fileInput.val('');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const button = $(this);
        const originalButtonText = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading & Processing File...');

        $.ajax({
            url: '/api/summarize/upload',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    redirectToChatWithSummarizationData({
                        context_key: response.context_key,
                        full_text_for_prompt: response.full_text_for_prompt,
                        prompt_text: response.prompt_text
                    });
                } else {
                    alert('Error: ' + (response.error || 'Could not process the file.'));
                    button.prop('disabled', false).html(originalButtonText);
                    fileInput.val('');
                }
            },
            error: function(jqXHR) {
                // ... (error handling remains similar)
                let errorMsg = 'An unknown error occurred while processing the file.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    errorMsg = jqXHR.responseJSON.error;
                } else if (jqXHR.responseText) {
                    try {
                        const err = JSON.parse(jqXHR.responseText);
                        if (err.message) errorMsg = err.message;
                    } catch (e) {}
                }
                alert('Error: ' + errorMsg);
                button.prop('disabled', false).html(originalButtonText);
                fileInput.val('');
            }
        });
    });
    // --- End Summarize Content Logic ---

    $('#modeDropdownButton + .dropdown-menu .dropdown-item').on('click', function (e) {
        e.preventDefault();
        // This is handled by the global applySelectedModel and its event listener
    });

    const dashboardPromptForm = $('#dashboard-prompt-form');
    const dashboardPromptInput = $('#dashboard-prompt-input');
    if (dashboardPromptForm.length && dashboardPromptInput.length) {
        dashboardPromptInput.focus();
    }

    $('.sidebar .nav').on('click', '.delete-chat-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const chatLinkElement = $(this).closest('a');
        const chatId = $(this).data('chat-id');
        const chatTitle = chatLinkElement.attr('title') || `Chat ID ${chatId}`;

        if (!chatId) {
            console.error('Could not find chat ID for deletion.');
            alert('Error: Could not determine which chat to delete.');
            return;
        }

        if (confirm(`Are you sure you want to delete the chat "${chatTitle}"? This cannot be undone.`)) {
            chatLinkElement.css('opacity', '0.5');
            $.ajax({
                url: `/api/chat/headers/${chatId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        chatLinkElement.fadeOut(300, function () {
                            $(this).remove();
                        });
                        // Optional: Redirect if deleting the currently active chat
                        if (window.location.pathname.includes(`/chat/${chatId}`)) {
                            window.location.href = '/chat'; // Redirect to new chat page
                        }
                    } else {
                        alert(data.error || 'Could not delete chat.');
                        console.error("Chat deletion error:", data.error);
                        chatLinkElement.css('opacity', '1');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('An error occurred while trying to delete the chat.');
                    console.error("AJAX Chat Deletion Error:", textStatus, errorThrown);
                    chatLinkElement.css('opacity', '1');
                }
            });
        }
    });

    $('#settingsModal').on('show.bs.modal', function () {
        const savedTheme = localStorage.getItem('theme') || 'light';
        $('#themeSelect').val(savedTheme.charAt(0).toUpperCase() + savedTheme.slice(1));

        const savedTone = localStorage.getItem('selectedPersonalityTone') || 'professional';
        $(`input[name="personalityTone"][value="${savedTone}"]`).prop('checked', true);

        const savedDefaultModel = localStorage.getItem('selectedLlmModel') || defaultModelId;
        $('#defaultModeSelect').val(savedDefaultModel);
        console.log('Loaded default model setting:', savedDefaultModel);
    });

    function updateUnreadCountInNav() {
        const countElement = $('#unread-messages-count');
        // Only run if the element exists (i.e., user is logged in)
        if (countElement.length) {
            $.ajax({
                url: '/api/user/unread-count',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.unread_count > 0) {
                        countElement.text(data.unread_count).show();
                    } else {
                        countElement.hide();
                    }
                },
                error: function(jqXHR) {
                    console.error("Could not fetch unread message count.", jqXHR.responseText);
                }
            });
        }
    }

    // Call it on page load
    updateUnreadCountInNav();

    $(document).on('click', '#account-switcher-submenu .dropdown-item', function(e) {
        e.preventDefault();
        const teamId = $(this).data('team-id');
        const linkItem = $(this);

        // If already selected (has a checkmark), do nothing.
        if (linkItem.closest('li').hasClass('selected')) {
            console.log('This account is already active.');
            return;
        }

        // Provide visual feedback that something is happening
        const originalHtml = linkItem.html();
        linkItem.html(`
            <div class="d-flex align-items-center">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Switching...
            </div>
        `);

        $.ajax({
            url: '/api/user/current-team',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                team_id: teamId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reload the page to apply the new context (team/personal) everywhere.
                    // This is the most robust way to ensure all data is correctly scoped.
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.error || 'Could not switch accounts.'));
                    linkItem.html(originalHtml); // Restore original content on failure
                }
            },
            error: function(jqXHR) {
                let errorMsg = 'An unknown error occurred while switching accounts.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    errorMsg = jqXHR.responseJSON.error;
                }
                alert('Error: ' + errorMsg);
                linkItem.html(originalHtml); // Restore original content on error
            }
        });
    });

    // --- START: Global File Attachment Logic (for Chat & Dashboard) ---
    const attachFileModal = $('#attachFileModal');
    if (attachFileModal.length) {
        const confirmAttachFilesBtn = $('#confirm-attach-files-btn');
        const attachMyFilesList = $('#attach-my-files-list');
        const attachTeamFilesList = $('#attach-team-files-list');
        const attachTeamSelectFilter = $('#attach-team-select-filter');

        function renderFileSelectItem(file) {
            const isSelected = window.BexApp.attachedFiles.has(file.id);
            const safeFileName = $('<div>').text(file.original_filename).html();
            const ownerName = file.owner ? $('<div>').text(file.owner.name).html() : 'N/A';
            return `
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center file-list-item-selectable ${isSelected ? 'selected' : ''}" data-file-id="${file.id}" data-file-name="${safeFileName}" data-mime-type="${file.mime_type}">
                <div class="d-flex align-items-center" style="min-width: 0;">
                    <i class="bi ${window.BexApp.getFileIcon(file.mime_type)} me-3 fs-4"></i>
                    <div class="text-truncate">
                        <strong class="d-block text-truncate">${safeFileName}</strong>
                        <small class="text-muted">Owner: ${ownerName}</small>
                    </div>
                </div>
                <i class="bi bi-check-circle-fill fs-5 text-primary selection-check" style="display: ${isSelected ? 'block' : 'none'};"></i>
            </a>`;
        }

        function loadMyFilesForAttachment() {
            attachMyFilesList.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div></div>');
            $.get('/api/user/files', function(files) {
                attachMyFilesList.empty();
                if (files.length > 0) {
                    files.forEach(file => attachMyFilesList.append(renderFileSelectItem(file)));
                } else {
                    attachMyFilesList.html('<p class="text-muted p-3 text-center">You have no files to attach.</p>');
                }
            });
        }

        function loadTeamFilesForAttachment(teamId) {
            if (!teamId) {
                attachTeamFilesList.html('<p class="text-muted p-3 text-center">Select a team to see its files.</p>');
                return;
            }
            attachTeamFilesList.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div></div>');
            $.get(`/api/teams/${teamId}/files`, function(files) {
                attachTeamFilesList.empty();
                if (files.length > 0) {
                    files.forEach(file => attachTeamFilesList.append(renderFileSelectItem(file)));
                } else {
                    attachTeamFilesList.html('<p class="text-muted p-3 text-center">No files have been shared with this team.</p>');
                }
            });
        }

        function loadUserTeamsForAttachment() {
            $.get('/api/user/teams', function(response) {
                attachTeamSelectFilter.empty().append('<option value="">-- Select a Team --</option>');
                if (response.teams && response.teams.length > 0) {
                    response.teams.forEach(team => {
                        attachTeamSelectFilter.append(`<option value="${team.id}">${$('<div>').text(team.name).html()}</option>`);
                    });
                }
                const currentTeamId = $('meta[name="current-team-id"]').attr('content');
                if (currentTeamId && currentTeamId !== '0') {
                    attachTeamSelectFilter.val(currentTeamId).trigger('change');
                } else {
                    loadTeamFilesForAttachment(null);
                }
            });
        }

        attachFileModal.on('show.bs.modal', function() {
            loadMyFilesForAttachment();
            loadUserTeamsForAttachment();
        });

        attachTeamSelectFilter.on('change', function() {
            loadTeamFilesForAttachment($(this).val());
        });

        $(document).on('click', '#attachFileModal .file-list-item-selectable', function(e) {
            e.preventDefault();
            const item = $(this);
            const fileId = item.data('file-id');
            const fileName = item.data('file-name');
            const mimeType = item.data('mime-type');
            if (item.hasClass('selected')) {
                item.removeClass('selected');
                item.find('.selection-check').hide();
                window.BexApp.attachedFiles.delete(fileId);
            } else {
                item.addClass('selected');
                item.find('.selection-check').show();
                window.BexApp.attachedFiles.set(fileId, { name: fileName, mime_type: mimeType });
            }
        });

        confirmAttachFilesBtn.on('click', function() {
            window.BexApp.renderFilePills();
            attachFileModal.modal('hide');
        });

        $(document).on('click', '#file-pills-container .remove-file-pill', function() {
            const fileId = $(this).data('id');
            window.BexApp.attachedFiles.delete(fileId);
            window.BexApp.renderFilePills();
        });

        // --- Dashboard Form Submission Logic ---
        const dashboardPromptForm = $('#dashboard-prompt-form');
        if (dashboardPromptForm.length) {
            dashboardPromptForm.on('submit', function (e) {
                e.preventDefault();
                const dashboardPromptInput = $('#dashboard-prompt-input');
                const promptText = dashboardPromptInput.val().trim();
                const attachedFileIds = [...window.BexApp.attachedFiles.keys()];

                if (!promptText && attachedFileIds.length === 0) {
                    return;
                }

                const sendButton = $('#dashboard-send-button');
                const originalButtonHtml = sendButton.html();
                sendButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                dashboardPromptInput.prop('disabled', true);

                const selectedModel = localStorage.getItem('selectedLlmModel') || 'openai/gpt-4o-mini';
                const selectedTone = localStorage.getItem('selectedPersonalityTone') || 'professional';

                $.ajax({
                    url: '/api/chat',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        message: promptText,
                        chat_header_id: null,
                        llm_model: selectedModel,
                        personality_tone: selectedTone,
                        attached_files: attachedFileIds
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.success && data.chat_header_id) {
                            window.location.href = '/chat/' + data.chat_header_id;
                        } else {
                            alert(data.error || 'An error occurred while starting the chat.');
                            sendButton.prop('disabled', false).html(originalButtonHtml);
                            dashboardPromptInput.prop('disabled', false);
                        }
                    },
                    error: function (jqXHR) {
                        let errorMsg = 'Could not start chat. Please try again.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                            errorMsg = jqXHR.responseJSON.error;
                        }
                        alert(errorMsg);
                        sendButton.prop('disabled', false).html(originalButtonHtml);
                        dashboardPromptInput.prop('disabled', false);
                    }
                });
            });
        }
    }
// --- END: Global File Attachment Logic ---


    // --- ADDED: Global File Preview Logic ---
    // This logic is used by both the main File Management page and the Team Workspace modal.
    const imagePreviewModalEl = document.getElementById('imagePreviewModal');
    const pdfPreviewModalEl = document.getElementById('pdfPreviewModal');

    if (imagePreviewModalEl && pdfPreviewModalEl) {
        const imagePreviewModal = new bootstrap.Modal(imagePreviewModalEl);
        const pdfPreviewModal = new bootstrap.Modal(pdfPreviewModalEl);

        $(document).on('click', '.preview-btn', function() {
            const fileId = $(this).data('file-id');
            const mimeType = $(this).data('mime-type');
            const previewUrl = `/api/files/${fileId}/preview`;

            if (!fileId || !mimeType) return;

            if (mimeType.startsWith('image/')) {
                $('#image-preview-content').attr('src', previewUrl);
                imagePreviewModal.show();
            } else if (mimeType === 'application/pdf') {
                $('#pdf-preview-content').attr('src', previewUrl);
                pdfPreviewModal.show();
            }
        });

        // Cleanup when modals are hidden to stop background loading
        $('#imagePreviewModal').on('hidden.bs.modal', function() {
            $('#image-preview-content').attr('src', '');
        });
        $('#pdfPreviewModal').on('hidden.bs.modal', function() {
            $('#pdf-preview-content').attr('src', '');
        });
    }
});
