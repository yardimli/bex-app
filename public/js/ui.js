/**
 * BexApp Global Object
 * Provides shared functions for file management across the application.
 */
window.BexApp = {
	attachedFiles: new Map(),

	/**
	 * Gets a Bootstrap Icon class based on the file's MIME type.
	 * @param {string} mimeType - The MIME type of the file.
	 * @returns {string} The corresponding icon class string.
	 */
	getFileIcon: function (mimeType) {
		// MODIFIED: Uses DaisyUI/Tailwind contextual colors.
		if (!mimeType) return 'bi-file-earmark-fill text-base-content/60';
		if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf-fill text-error';
		if (mimeType.includes('word')) return 'bi-file-earmark-word-fill text-primary';
		if (mimeType.includes('image')) return 'bi-file-earmark-image-fill text-info';
		if (mimeType.includes('text')) return 'bi-file-earmark-text-fill text-secondary';
		return 'bi-file-earmark-fill text-base-content/60';
	},

	/**
	 * Renders the attached file "pills" (badges) in the UI.
	 */
	renderFilePills: function () {
		const container = $('#file-pills-container');
		if (!container.length) return;

		container.empty();
		const fileIds = [];
		this.attachedFiles.forEach((file, id) => {
			// MODIFIED: Renders a DaisyUI badge with a close button.
			container.append(`
                <div class="badge badge-secondary gap-2 p-3">
                    <i class="bi ${this.getFileIcon(file.mime_type)}"></i>
                    <span>${$('<div>').text(file.name).html()}</span>
                    <button type="button" class="btn btn-ghost btn-circle btn-xs remove-file-pill" data-id="${id}" aria-label="Remove">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `);
			fileIds.push(id);
		});
		// Updates the hidden input with the list of attached file IDs.
		$('#attached-files-input').val(fileIds.length > 0 ? JSON.stringify(fileIds) : '');
	}
};

$(document).ready(function () {
	// --- Element Selectors ---
	const htmlElement = $('html');
	const defaultModelId = 'openai/gpt-4o-mini';
	const themeControllerCheckbox = $('#theme-controller-checkbox'); // MODIFIED: Selector for the theme checkbox.

	// --- Modal Selectors (FIX) ---
	// MODIFIED: Added definitions for all modal dialogs. This fixes them not opening.
	const recentMeetingsModal = document.getElementById('recentMeetingsModal');
	const myNotesModal = document.getElementById('myNotesModal');
	const myRecordingsModal = document.getElementById('myRecordingsModal');
	const summarizeContentModal = document.getElementById('summarizeContentModal');
	const transcribeModal = document.getElementById('transcribeModal');
	const teamFilesModal = document.getElementById('teamFilesModal');
	const settingsModal = document.getElementById('settingsModal');
	const imagePreviewModal = document.getElementById('imagePreviewModal');
	const pdfPreviewModal = document.getElementById('pdfPreviewModal');

	// --- Theme Toggle Logic ---
	// MODIFIED: Refactored to correctly work with DaisyUI's theme-controller component.
	function initializeTheme() {
		const savedTheme = localStorage.getItem('theme');
		// Fallback to system preference if no theme is saved
		const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
		const theme = savedTheme || systemTheme;

		htmlElement.attr('data-theme', theme);
		// Sync the checkbox's state with the determined theme on page load.
		if (themeControllerCheckbox.length) {
			themeControllerCheckbox.prop('checked', theme === 'dark');
		}
	}

	// Initialize theme on page load.
	initializeTheme();

	// The theme-controller checkbox handles the data-theme attribute change automatically.
	// We just need to listen to its change to save the new preference to localStorage.
	if (themeControllerCheckbox.length) {
		themeControllerCheckbox.on('change', function () {
			const theme = $(this).is(':checked') ? 'dark' : 'light';
			localStorage.setItem('theme', theme);
		});
	}

	// --- Modal Opening Logic ---
	// MODIFIED: Use the defined modal variables and add checks to ensure they exist.
	if (recentMeetingsModal) $('#meetingSummaryButton').on('click', () => recentMeetingsModal.showModal());
	// Note: The #actionItemsButton listener is correctly in action-items.js, so it's not needed here.
	if (myNotesModal) $('#myNotesButton').on('click', () => myNotesModal.showModal());
	if (myRecordingsModal) $('#myRecordingsButton').on('click', () => myRecordingsModal.showModal());
	if (summarizeContentModal) $('#summarizeButton').on('click', () => summarizeContentModal.showModal());
	if (transcribeModal) $('#transcribeButton').on('click', () => transcribeModal.showModal());
	if (teamFilesModal) $('#teamWorkspaceButton').on('click', () => teamFilesModal.showModal());

	// Settings modal can be opened from two places.
	if (settingsModal) {
		$('#settingsButton, #settingsButtonFromDropdown').on('click', (e) => {
			e.preventDefault();
			// Populate settings form with saved values before showing.
			const savedTheme = localStorage.getItem('theme') || 'light';
			$('#themeSelect').val(savedTheme);
			const savedTone = localStorage.getItem('selectedPersonalityTone') || 'professional';
			$(`input[name="personalityTone"][value="${savedTone}"]`).prop('checked', true);
			const savedDefaultModel = localStorage.getItem('selectedLlmModel') || defaultModelId;
			$('#defaultModeSelect').val(savedDefaultModel);

			settingsModal.showModal();
		});
	}

	// --- Model Selector Dropdown Logic ---
	// MODIFIED: Corrected selectors and refactored logic to handle model selection and UI updates properly.
	const modeDropdownMenu = $('#mode-dropdown-menu');
	const selectedModelNameSpan = $('#selected-model-name');

	if (modeDropdownMenu.length) {
		/**
		 * Updates the dropdown's appearance (button text and active item).
		 * @param {jQuery} selectedLi - The jQuery object for the selected list item.
		 * @param {string} displayName - The text to display on the button.
		 */
		function updateDropdownSelection(selectedLi, displayName) {
			if (selectedModelNameSpan.length) {
				selectedModelNameSpan.text(displayName);
			}
			modeDropdownMenu.find('li').removeClass('bordered');
			if (selectedLi && selectedLi.length) {
				selectedLi.addClass('bordered');
			}
		}

		/**
		 * Applies a model selection to the UI, typically on page load or after settings change.
		 * It finds the first matching item in the dropdown for a given model ID.
		 * @param {string} modelId - The ID of the model to apply.
		 */
		function applyModelToDropdown(modelId) {
			// Find the first list item that matches the model ID.
			const selectedLi = modeDropdownMenu.find(`li[data-model-id="${modelId}"]`).first();
			let displayName = 'Smart Mode'; // Default display name.

			if (selectedLi.length) {
				const link = selectedLi.find('a');
				// Use the specific display name from the found item, or its text.
				displayName = link.data('display-name') || link.text().trim();
				updateDropdownSelection(selectedLi, displayName);
			} else {
				// If the saved model isn't in the list, fall back to the hardcoded default.
				const defaultLi = modeDropdownMenu.find(`li[data-model-id="${defaultModelId}"]`).first();
				if (defaultLi.length) {
					const link = defaultLi.find('a');
					displayName = link.data('display-name') || link.text().trim();
					updateDropdownSelection(defaultLi, displayName);
				} else {
					// If even the default is not found, just update the text.
					updateDropdownSelection(null, displayName);
					console.error("Default model item not found in dropdown!");
				}
			}
		}

		// Event listener for when a user clicks an item in the dropdown.
		modeDropdownMenu.on('click', 'a', function (e) {
			e.preventDefault();
			const clickedLink = $(this);
			const liParent = clickedLink.closest('li');
			const selectedModelId = liParent.data('model-id');

			if (selectedModelId) {
				// Save the selected model ID to local storage.
				localStorage.setItem('selectedLlmModel', selectedModelId);

				// Get the display name directly from the clicked item.
				const displayName = clickedLink.data('display-name') || clickedLink.text().trim();

				// Update the UI to reflect the clicked item.
				updateDropdownSelection(liParent, displayName);

				// Close the dropdown by removing focus from the active element.
				if (document.activeElement) document.activeElement.blur();
			}
		});

		// On page load, apply the saved model or the default one.
		const savedModel = localStorage.getItem('selectedLlmModel');
		applyModelToDropdown(savedModel || defaultModelId);
	}

	// --- Save Settings Logic ---
	$('#saveSettingsButton').on('click', function () {
		const selectedDefaultModel = $('#defaultModeSelect').val();
		const selectedThemeValue = $('#themeSelect').val();
		const selectedToneValue = $('input[name="personalityTone"]:checked').val();

		// When saving from settings, we need to manually set the theme
		htmlElement.attr('data-theme', selectedThemeValue);
		if (themeControllerCheckbox.length) {
			themeControllerCheckbox.prop('checked', selectedThemeValue === 'dark');
		}
		localStorage.setItem('theme', selectedThemeValue);

		localStorage.setItem('selectedPersonalityTone', selectedToneValue);
		localStorage.setItem('selectedLlmModel', selectedDefaultModel);

		// MODIFIED: Immediately update the model selector dropdown UI if it exists.
		if (modeDropdownMenu.length) {
			applyModelToDropdown(selectedDefaultModel);
		}

		settingsModal.close(); // MODIFIED: DaisyUI close method
	});

	// --- Summarize Content Logic ---
	function redirectToChatWithSummarizationData(data) {
		const chatUrl = '/chat';
		let redirectUrl;

		if (data.context_key) {
			redirectUrl = `${chatUrl}?summarize_key=${encodeURIComponent(data.context_key)}&prompt_text=${encodeURIComponent(data.prompt_text || '')}`;
		} else if (data.full_text_for_prompt) {
			const promptText = (data.prompt_text || '') + data.full_text_for_prompt;
			redirectUrl = `${chatUrl}?prompt=${encodeURIComponent(promptText)}`;
		} else {
			alert('Error: Could not prepare summarization data.');
			return;
		}

		if (summarizeContentModal) summarizeContentModal.close(); // MODIFIED: DaisyUI close method
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
			new URL(url);
		} catch (_) {
			alert('Please enter a valid URL.');
			return;
		}

		const button = $(this);
		const originalButtonText = button.html();
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Fetching...'); // MODIFIED: DaisyUI spinner

		$.ajax({
			url: '/api/summarize/url',
			method: 'POST',
			data: {_token: $('meta[name="csrf-token"]').attr('content'), url: url},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					redirectToChatWithSummarizationData(response);
				} else {
					alert('Error: ' + (response.error || 'Could not process the URL.'));
					button.prop('disabled', false).html(originalButtonText);
				}
			},
			error: function (jqXHR) {
				const errorMsg = jqXHR.responseJSON?.error || 'An unknown error occurred.';
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
		const promptText = `Summarize the following text:\n\n${text}`;
		const redirectUrl = `/chat?prompt=${encodeURIComponent(promptText)}`;
		if (summarizeContentModal) summarizeContentModal.close(); // MODIFIED: DaisyUI close method
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
		// Client-side validation remains the same.
		const formData = new FormData();
		formData.append('file', file);
		formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

		const button = $(this);
		const originalButtonText = button.html();
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Uploading...'); // MODIFIED: DaisyUI spinner

		$.ajax({
			url: '/api/summarize/upload',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					redirectToChatWithSummarizationData(response);
				} else {
					alert('Error: ' + (response.error || 'Could not process the file.'));
					button.prop('disabled', false).html(originalButtonText);
					fileInput.val('');
				}
			},
			error: function (jqXHR) {
				const errorMsg = jqXHR.responseJSON?.error || 'An unknown error occurred.';
				alert('Error: ' + errorMsg);
				button.prop('disabled', false).html(originalButtonText);
				fileInput.val('');
			}
		});
	});

	// --- Chat History Deletion ---
	// Logic is unchanged, but selectors target the new menu structure.
	$('.sidebar .menu').on('click', '.delete-chat-btn', function (e) {
		e.preventDefault();
		e.stopPropagation();
		const chatLinkElement = $(this).closest('a');
		const chatListItem = chatLinkElement.closest('li');
		const chatId = $(this).data('chat-id');
		const chatTitle = chatLinkElement.attr('title') || `Chat ID ${chatId}`;

		if (!chatId) {
			alert('Error: Could not determine which chat to delete.');
			return;
		}

		if (confirm(`Are you sure you want to delete the chat "${chatTitle}"? This cannot be undone.`)) {
			chatListItem.css('opacity', '0.5');
			$.ajax({
				url: `/api/chat/headers/${chatId}`,
				method: 'DELETE',
				data: {_token: $('meta[name="csrf-token"]').attr('content')},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						chatListItem.fadeOut(300, function () {
							$(this).remove();
						});
						if (window.location.pathname.includes(`/chat/${chatId}`)) {
							window.location.href = '/chat';
						}
					} else {
						alert(data.error || 'Could not delete chat.');
						chatListItem.css('opacity', '1');
					}
				},
				error: function () {
					alert('An error occurred while trying to delete the chat.');
					chatListItem.css('opacity', '1');
				}
			});
		}
	});

	// --- Unread Message Count (Logic Unchanged) ---
	function updateUnreadCountInNav() {
		const countElement = $('#unread-messages-count');
		if (countElement.length) {
			$.get('/api/user/unread-count', function (data) {
				if (data.unread_count > 0) {
					countElement.text(data.unread_count).show();
				} else {
					countElement.hide();
				}
			});
		}
	}

	updateUnreadCountInNav();

	// --- Account Switcher (Logic Unchanged) ---
	$(document).on('click', '#account-switcher-submenu a', function (e) {
		e.preventDefault();
		const linkItem = $(this);
		if (linkItem.closest('li').hasClass('bordered')) return; // 'bordered' is the new 'active'
		const teamId = linkItem.data('team-id');
		const originalHtml = linkItem.html();
		linkItem.html('<span class="loading loading-spinner loading-sm"></span> Switching...');
		$.ajax({
			url: '/api/user/current-team',
			method: 'POST',
			data: {_token: $('meta[name="csrf-token"]').attr('content'), team_id: teamId},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					window.location.reload();
				} else {
					alert('Error: ' + (response.error || 'Could not switch accounts.'));
					linkItem.html(originalHtml);
				}
			},
			error: function () {
				alert('An unknown error occurred while switching accounts.');
				linkItem.html(originalHtml);
			}
		});
	});

	// --- Global File Attachment Logic ---
	const attachFileModal = document.getElementById('attachFileModal');
	if (attachFileModal) {
		const confirmAttachFilesBtn = $('#confirm-attach-files-btn');
		const attachMyFilesList = $('#attach-my-files-list');
		const attachTeamFilesList = $('#attach-team-files-list');
		const attachTeamSelectFilter = $('#attach-team-select-filter');


        const allowedMimeTypesForAttachment = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
		function renderFileSelectItem(file) {
            const isAllowed = allowedMimeTypesForAttachment.includes(file.mime_type) ||
                file.original_filename.toLowerCase().endsWith('.docx');
			const isSelected = window.BexApp.attachedFiles.has(file.id);
			const safeFileName = $('<div>').text(file.original_filename).html();
			const ownerName = file.owner ? $('<div>').text(file.owner.name).html() : 'N/A';

            const selectableClass = isAllowed ? 'file-list-item-selectable' : '';
            const hoverClass = isAllowed ? 'hover:bg-base-200' : '';
            const stateClass = isSelected ? 'bg-base-300' : '';
            const disabledClass = !isAllowed ? 'opacity-50 cursor-not-allowed' : '';
            const titleAttr = !isAllowed ? 'title="This file type cannot be attached for context (only PDF, DOCX, TXT are supported)."' : '';

			// MODIFIED: Uses Tailwind/DaisyUI classes for the list item.
            return `
 <a href="#" class="flex justify-between items-center p-2 rounded-lg ${hoverClass} ${stateClass} ${disabledClass} ${selectableClass}" ${titleAttr} data-file-id="${file.id}" data-file-name="${safeFileName}" data-mime-type="${file.mime_type}">
 <div class="flex items-center min-w-0">
 <i class="bi ${window.BexApp.getFileIcon(file.mime_type)} me-3 text-2xl"></i>
 <div class="truncate">
 <strong class="block truncate">${safeFileName}</strong>
 <small class="text-base-content/70">Owner: ${ownerName}</small>
 </div>
 </div>
 <i class="bi bi-check-circle-fill text-xl text-primary selection-check" style="display: ${isSelected ? 'block' : 'none'};"></i>
 </a>`;
		}

		function loadMyFilesForAttachment() {
			attachMyFilesList.html('<div class="text-center p-3"><span class="loading loading-spinner"></span></div>');
			$.get('/api/user/files', function (files) {
				attachMyFilesList.empty();
				if (files.length > 0) {
					files.forEach(file => attachMyFilesList.append(renderFileSelectItem(file)));
				} else {
					attachMyFilesList.html('<p class="text-base-content/60 p-3 text-center">You have no files to attach.</p>');
				}
			});
		}

		function loadTeamFilesForAttachment(teamId) {
			if (!teamId) {
				attachTeamFilesList.html('<p class="text-base-content/60 p-3 text-center">Select a team to see its files.</p>');
				return;
			}
			attachTeamFilesList.html('<div class="text-center p-3"><span class="loading loading-spinner"></span></div>');
			$.get(`/api/teams/${teamId}/files`, function (files) {
				attachTeamFilesList.empty();
				if (files.length > 0) {
					files.forEach(file => attachTeamFilesList.append(renderFileSelectItem(file)));
				} else {
					attachTeamFilesList.html('<p class="text-base-content/60 p-3 text-center">No files have been shared with this team.</p>');
				}
			});
		}

		function loadUserTeamsForAttachment() {
			$.get('/api/user/teams', function (response) {
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

		// MODIFIED: Open modal with button click instead of data attributes.
		$('#attach-file-btn').on('click', function () {
			loadMyFilesForAttachment();
			loadUserTeamsForAttachment();
			attachFileModal.showModal();
		});

		attachTeamSelectFilter.on('change', function () {
			loadTeamFilesForAttachment($(this).val());
		});

		$(document).on('click', '#attachFileModal .file-list-item-selectable', function (e) {
			e.preventDefault();
			const item = $(this);
			const fileId = item.data('file-id');
			const fileName = item.data('file-name');
			const mimeType = item.data('mime-type');
			if (item.hasClass('bg-base-300')) {
				item.removeClass('bg-base-300');
				item.find('.selection-check').hide();
				window.BexApp.attachedFiles.delete(fileId);
			} else {
				item.addClass('bg-base-300');
				item.find('.selection-check').show();
				window.BexApp.attachedFiles.set(fileId, {name: fileName, mime_type: mimeType});
			}
		});

		confirmAttachFilesBtn.on('click', function () {
			window.BexApp.renderFilePills();
			// No need to close modal here, as it's handled by the form method="dialog"
		});

		$(document).on('click', '#file-pills-container .remove-file-pill', function () {
			const fileId = $(this).data('id');
			window.BexApp.attachedFiles.delete(fileId);
			window.BexApp.renderFilePills();
		});

		// --- Dashboard Form Submission Logic ---
		const dashboardPromptForm = $('#dashboard-prompt-form');
		if (dashboardPromptForm.length) {
			dashboardPromptForm.on('submit', function (e) {
				e.preventDefault();
				const promptText = $('#dashboard-prompt-input').val().trim();
				const attachedFileIds = [...window.BexApp.attachedFiles.keys()];
				if (!promptText && attachedFileIds.length === 0) return;

				const sendButton = $('#dashboard-send-button');
				const originalButtonHtml = sendButton.html();
				sendButton.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span>');
				$('#dashboard-prompt-input').prop('disabled', true);

				const selectedModel = localStorage.getItem('selectedLlmModel') || defaultModelId;
				const selectedTone = localStorage.getItem('selectedPersonalityTone') || 'professional';

				$.ajax({
					url: '/api/chat',
					method: 'POST',
					data: {
						_token: $('meta[name="csrf-token"]').attr('content'),
						message: promptText,
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
							$('#dashboard-prompt-input').prop('disabled', false);
						}
					},
					error: function () {
						alert('Could not start chat. Please try again.');
						sendButton.prop('disabled', false).html(originalButtonHtml);
						$('#dashboard-prompt-input').prop('disabled', false);
					}
				});
			});
		}
	}

	// --- Global File Preview Logic ---
	$(document).on('click', '.preview-btn', function () {
		const fileId = $(this).data('file-id');
		const mimeType = $(this).data('mime-type');
		const previewUrl = `/api/files/${fileId}/preview`;
		if (!fileId || !mimeType) return;

		if (mimeType.startsWith('image/')) {
			$('#image-preview-content').attr('src', previewUrl);
			if (imagePreviewModal) imagePreviewModal.showModal(); // MODIFIED
		} else if (mimeType === 'application/pdf') {
			$('#pdf-preview-content').attr('src', previewUrl);
			if (pdfPreviewModal) pdfPreviewModal.showModal(); // MODIFIED
		}
	});
});
