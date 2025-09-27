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
	const themeControllerCheckbox = $('#theme-controller-checkbox');
	
	const recentMeetingsModal = document.getElementById('recentMeetingsModal');
	const myNotesModal = document.getElementById('myNotesModal');
	const myRecordingsModal = document.getElementById('myRecordingsModal');
	const summarizeContentModal = document.getElementById('summarizeContentModal');
	const transcribeModal = document.getElementById('transcribeModal');
	const teamFilesModal = document.getElementById('teamFilesModal');
	const settingsModal = document.getElementById('settingsModal');
	const imagePreviewModal = document.getElementById('imagePreviewModal');
	const pdfPreviewModal = document.getElementById('pdfPreviewModal');
	const groupChatSetupModal = document.getElementById('groupChatSetupModal');
	
	
	$('#new-chat-button').on('click', function(e) {
		e.preventDefault();
		const newChatModal = document.getElementById('newChatOptionsModal');
		if (newChatModal) {
			newChatModal.showModal();
		}
	});
	
	$('#start-group-chat-link').on('click', function(e) {
		e.preventDefault();
		const currentTeamId = $('meta[name="current-team-id"]').attr('content');
		const currentUserId = parseInt($('meta[name="current-user-id"]').attr('content'), 10);
		
		const newChatModal = document.getElementById('newChatOptionsModal');
		if (newChatModal) newChatModal.close();
		
		if (currentTeamId && currentTeamId !== '0') {
			// Team context: fetch members and show setup modal
			const participantList = $('#group-chat-participant-list');
			participantList.html('<div class="text-center"><span class="loading loading-spinner"></span></div>');
			if (groupChatSetupModal) groupChatSetupModal.showModal();
			
			$.get(`/api/teams/${currentTeamId}/members`, function(members) {
				participantList.empty();
				if (members && members.length > 1) {
					members.forEach(member => {
						// Don't let users select themselves
						if (member.id === currentUserId) return;
						
						const safeName = $('<div>').text(member.name).html();
						participantList.append(`
                            <label class="label cursor-pointer hover:bg-base-200 rounded-lg p-2">
                                <span class="label-text">${safeName}</span>
                                <input type="checkbox" value="${member.id}" class="checkbox checkbox-primary participant-checkbox" />
                            </label>
                        `);
					});
				} else {
					participantList.html('<p class="text-base-content/60 p-3 text-center">No other members in this team to start a chat with.</p>');
				}
			});
		} else {
			const requiredModal = document.getElementById('groupChatRequiredModal');
			if (requiredModal) {
				requiredModal.showModal();
			}
		}
	});
	
	$('#create-group-chat-btn').on('click', function() {
		const button = $(this);
		const title = $('#group-chat-title').val().trim();
		const participant_ids = $('.participant-checkbox:checked').map(function() {
			return $(this).val();
		}).get();
		const team_id = $('meta[name="current-team-id"]').attr('content');
		if (!title) {
			alert('Please enter a name for the chat.');
			return;
		}
		if (participant_ids.length === 0) {
			alert('Please select at least one other participant.');
			return;
		}
		
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Creating...');
		
		$.ajax({
			url: '/api/group-chat/setup',
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				team_id: team_id,
				title: title,
				participant_ids: participant_ids
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					window.location.href = response.redirect_url;
				} else {
					alert(response.error || 'Failed to create group chat.');
					button.prop('disabled', false).html('Create Chat');
				}
			},
			error: function(jqXHR) {
				alert(jqXHR.responseJSON?.error || 'An unknown error occurred.');
				button.prop('disabled', false).html('Create Chat');
			}
		});
	});
	
	
	// --- Theme Toggle Logic ---
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
	if (recentMeetingsModal) $('#meetingSummaryButton').on('click', () => recentMeetingsModal.showModal());
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
	
	/**
	 * Updates the appearance of all model selector dropdowns.
	 * @param {string|null} modelId - The model ID to mark as selected.
	 * @param {string} displayName - The text to display on the dropdown buttons.
	 */
	function updateAllDropdowns(modelId, displayName) {
		// Update the button text on all dropdowns.
		$('.selected-model-name').text(displayName);
		
		// Update the 'bordered' class on all dropdown menus.
		$('.mode-dropdown-menu').each(function() {
			const menu = $(this);
			menu.find('li').removeClass('bordered');
			if (modelId) {
				const itemToSelect = menu.find(`li[data-model-id="${modelId}"]`).first();
				if (itemToSelect.length) {
					itemToSelect.addClass('bordered');
				}
			}
		});
	}
	
	/**
	 * Applies a model selection to the UI, typically on page load or after settings change.
	 * It finds the model info from the first available dropdown and applies it to all.
	 * @param {string} modelId - The ID of the model to apply.
	 */
	function applyModelToAllDropdowns(modelId) {
		let displayName = 'Smart Mode'; // Default display name.
		let foundModelId = modelId;
		
		// Find the first available dropdown menu to source the display name from.
		const anyMenu = $('.mode-dropdown-menu').first();
		if (!anyMenu.length) return; // Exit if no dropdowns are on the page.
		
		const selectedLi = anyMenu.find(`li[data-model-id="${modelId}"]`).first();
		
		if (selectedLi.length) {
			const link = selectedLi.find('a');
			displayName = link.data('display-name') || link.text().trim();
		} else {
			// If the saved model isn't in the list, fall back to the hardcoded default.
			const defaultLi = anyMenu.find(`li[data-model-id="${defaultModelId}"]`).first();
			if (defaultLi.length) {
				const link = defaultLi.find('a');
				displayName = link.data('display-name') || link.text().trim();
				foundModelId = defaultModelId; // Use the default model ID for highlighting.
			} else {
				// If even the default is not found, just update the text.
				console.error("Default model item not found in dropdown!");
				foundModelId = null; // Nothing to highlight.
			}
		}
		updateAllDropdowns(foundModelId, displayName);
	}
	
	// Event listener for when a user clicks an item in any model dropdown.
	$('.mode-dropdown-menu').on('click', 'a', function (e) {
		e.preventDefault();
		const clickedLink = $(this);
		const liParent = clickedLink.closest('li');
		const selectedModelId = liParent.data('model-id');
		
		if (selectedModelId) {
			// Save the selected model ID to local storage.
			localStorage.setItem('selectedLlmModel', selectedModelId);
			
			// Get the display name directly from the clicked item.
			const displayName = clickedLink.data('display-name') || clickedLink.text().trim();
			
			// Update all dropdowns to reflect the new selection.
			updateAllDropdowns(selectedModelId, displayName);
			
			// Close the dropdown by removing focus from the active element.
			if (document.activeElement) document.activeElement.blur();
		}
	});
	
	// On page load, apply the saved model or the default one to all dropdowns.
	const savedModel = localStorage.getItem('selectedLlmModel');
	applyModelToAllDropdowns(savedModel || defaultModelId);
	
	
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
		
		applyModelToAllDropdowns(selectedDefaultModel);
		
		settingsModal.close();
	});
	
	// --- Summarize Content Logic ---
	function redirectToChatWithSummarizationData(data) {
		const chatUrl = '/chat';
		let redirectUrl;
		
		if (data.context_key) {
			// This is now the ONLY supported method for summarization redirects.
			redirectUrl = `${chatUrl}?summarize_key=${encodeURIComponent(data.context_key)}`;
		} else {
			// This else block now catches any summarization attempt that fails to produce a context key.
			alert('Error: Could not prepare summarization data.');
			return;
		}
		
		if (summarizeContentModal) summarizeContentModal.close();
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
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Fetching...');
		
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
		
		const button = $(this);
		const originalButtonText = button.html();
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Processing...');
		
		$.ajax({
			url: '/api/summarize/text',
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				text: text
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					redirectToChatWithSummarizationData(response);
				} else {
					alert('Error: ' + (response.error || 'Could not process the text.'));
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
		button.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Uploading...');
		
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
							if ($('#chat-history-list li:not(#no-chat-results, #no-chat-history)').length === 0) {
								$('#no-chat-history').show();
							}
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
	
	// --- Chat History Deletion ---
	const confirmationModal = document.getElementById('confirmationModal');
	const confirmationModalTitle = $('#confirmationModalTitle');
	const confirmationModalText = $('#confirmationModalText');
	const confirmationModalConfirm = $('#confirmationModalConfirm');
	let itemToDelete = null; // To hold context for the confirmation handler
	
	// When a delete button is clicked, set up and show the modal
	$('#chat-history-list').on('click', '.delete-chat-btn', function (e) {
		e.preventDefault();
		e.stopPropagation();
		
		const button = $(this);
		const chatLinkElement = $(this).closest('a');
		const chatListItem = chatLinkElement.closest('li');
		const chatId = $(this).data('chat-id');
		const chatTitle = chatLinkElement.attr('title') || `Chat ID ${chatId}`;
		const chatType = button.data('type') || 'personal';
		
		if (!chatId) {
			alert('Error: Could not determine which chat to delete.');
			return;
		}
		
		// Store context for the confirmation handler
		itemToDelete = {
			id: chatId,
			element: chatListItem,
			type: chatType
		};
		
		// Populate and show the modal
		confirmationModalTitle.text(`Delete ${chatType === 'group' ? 'Group' : 'Personal'} Chat`);
		// Sanitize title before inserting into HTML
		const safeChatTitle = $('<div>').text(chatTitle).html();
		confirmationModalText.html(`Are you sure you want to delete the chat "<strong>${safeChatTitle}</strong>"?<br>This action cannot be undone.`);
		if (confirmationModal) {
			confirmationModal.showModal();
		}
	});
	
	// When the confirm button in the modal is clicked, perform the deletion
	if (confirmationModal) {
		confirmationModalConfirm.on('click', function() {
			// The modal form has method="dialog", so it will close automatically.
			// We just need to perform the action.
			if (!itemToDelete) return;
			
			const chatListItem = itemToDelete.element;
			const chatId = itemToDelete.id;
			const apiUrl = itemToDelete.type === 'group'
				? `/api/group-chat/headers/${chatId}`
				: `/api/chat/headers/${chatId}`;
			const redirectUrl = itemToDelete.type === 'group'
				? `/team/${$('meta[name="current-team-id"]').attr('content')}/group-chat`
				: '/chat';
			
			
			chatListItem.css('opacity', '0.5');
			
			$.ajax({
				url: apiUrl,
				method: 'DELETE',
				data: { _token: $('meta[name="csrf-token"]').attr('content') },
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						chatListItem.fadeOut(300, function () {
							$(this).remove();
							if ($('#chat-history-list li:not(#no-chat-results, #no-chat-history)').length === 0) {
								$('#no-chat-history').show();
							}
						});
						if (window.location.pathname.includes(`/${chatId}`)) {
							window.location.href = redirectUrl;
						}
					} else {
						alert(data.error || 'Could not delete chat.');
						chatListItem.css('opacity', '1');
					}
				},
				error: function () {
					alert('An error occurred while trying to delete the chat.');
					chatListItem.css('opacity', '1');
				},
				complete: function() {
					itemToDelete = null; // Reset after action
				}
			});
		});
		
		// Also clear the itemToDelete if the modal is closed via Cancel or ESC
		confirmationModal.addEventListener('close', () => {
			itemToDelete = null;
		});
	}
	
	// --- Chat History Search ---
	const chatSearchInput = $('#chat-search-input');
	const chatHistoryList = $('#chat-history-list');
	const chatHistoryLoader = $('#chat-history-loader');
	const noChatResultsMsg = $('#no-chat-results');
	const noChatHistoryMsg = $('#no-chat-history');
	let searchDebounceTimer;
	
	function renderChatHistoryList(headers) {
		// Clear everything except the placeholder items
		chatHistoryList.find('li:not(#chat-history-loader, #no-chat-history, #no-chat-results)').remove();
		chatHistoryLoader.hide();
		noChatHistoryMsg.hide();
		noChatResultsMsg.hide();
		
		if (!headers || headers.length === 0) {
			// If the list is empty, determine if it's because of no history or no search results
			if (chatSearchInput.val().trim().length > 0) {
				noChatResultsMsg.show();
			} else {
				noChatHistoryMsg.show();
			}
			return;
		}
		
		// Get the current active chat ID from the URL, if present
		const pathParts = window.location.pathname.split('/');
		const activeChatId = pathParts.length > 2 ? parseInt(pathParts[pathParts.length - 1], 10) : null;
		const currentTeamId = $('meta[name="current-team-id"]').attr('content');
		const isTeamContext = currentTeamId && currentTeamId !== '0';
		const currentUserId = parseInt($('meta[name="current-user-id"]').attr('content'), 10);
		
		headers.forEach(header => {
			const safeTitle = $('<div>').text(header.title).html();
			const truncatedTitle = safeTitle.length > 25 ? safeTitle.substring(0, 22) + '...' : safeTitle;
			const isActive = header.id === activeChatId;
			const chatUrl = isTeamContext ? `/team/${currentTeamId}/group-chat/${header.id}` : `/chat/${header.id}`;
			const deleteType = isTeamContext ? 'group' : 'personal';
			
			let deleteButtonHtml = '';
			if (deleteType === 'personal' || (deleteType === 'group' && header.creator_id === currentUserId)) {
				deleteButtonHtml = `
                <button class="btn btn-ghost btn-xs btn-circle delete-chat-btn" data-chat-id="${header.id}" data-type="${deleteType}">
                    <i class="bi bi-trash text-error"></i>
                </button>`;
			}
			
			const newLinkHtml = `
            <li>
                <a href="${chatUrl}" id="chat-link-${header.id}" title="${safeTitle}" class="justify-between ${isActive ? 'active' : ''}">
                    <span class="truncate">${truncatedTitle}</span>
                    ${deleteButtonHtml}
                </a>
            </li>`;
			
			chatHistoryList.append(newLinkHtml);
		});
	}
	
	function loadChatHistory(searchTerm = '') {
		chatHistoryLoader.show();
		noChatHistoryMsg.hide();
		noChatResultsMsg.hide();
		chatHistoryList.find('li:not(#chat-history-loader, #no-chat-history, #no-chat-results)').remove();
		
		const currentTeamId = $('meta[name="current-team-id"]').attr('content');
		let url, data = {};
		
		if (currentTeamId && currentTeamId !== '0') {
			// Team context: load group chats
			url = searchTerm ? `/api/team/${currentTeamId}/group-chats/search` : `/api/team/${currentTeamId}/group-chats`;
			if (searchTerm) data.q = searchTerm;
		} else {
			// Personal context: load personal chats
			url = searchTerm ? '/api/chat/search' : '/api/chat/headers';
			if (searchTerm) data.q = searchTerm;
		}
		
		$.ajax({
			url: url,
			method: 'GET',
			data: data,
			dataType: 'json',
			success: function(response) {
				renderChatHistoryList(response);
			},
			error: function() {
				chatHistoryLoader.hide();
				chatHistoryList.find('li:not(#chat-history-loader, #no-chat-history, #no-chat-results)').remove();
				noChatHistoryMsg.show().find('span').text('Error loading history.');
			}
		});
	}
	
	if (chatSearchInput.length && chatHistoryList.length) {
		
		const savedSearchTerm = sessionStorage.getItem('chatSearchTerm') || '';
		chatSearchInput.val(savedSearchTerm);
		// Initial load of chat history
		loadChatHistory(savedSearchTerm);
		
		// Event listener for the search input
		chatSearchInput.on('keyup', function () {
			clearTimeout(searchDebounceTimer);
			const searchTerm = $(this).val().trim();
			
			searchDebounceTimer = setTimeout(() => {
				sessionStorage.setItem('chatSearchTerm', searchTerm);
				// Only search if term is empty (to reload) or has 2+ characters
				if (searchTerm.length === 0 || searchTerm.length >= 2) {
					loadChatHistory(searchTerm);
				}
			}, 400); // 400ms debounce delay
		});
	}
	
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
	
	$(document).on('click', '.account-switch-btn', function(e) {
		e.preventDefault();
		const button = $(this);
		// Do nothing if the button is already active or disabled
		if (button.hasClass('btn-active') || button.prop('disabled')) {
			return;
		}
		
		const teamId = button.data('team-id');
		const originalHtml = button.html();
		
		// Disable all switcher buttons and show a loading spinner on the clicked one
		$('.account-switch-btn').prop('disabled', true);
		button.html('<span class="loading loading-spinner loading-sm"></span>');
		
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
					// Reload the page to apply the new team context everywhere
					window.location.reload();
				} else {
					alert('Error: ' + (response.error || 'Could not switch accounts.'));
					// On failure, re-enable buttons and restore the original content
					$('.account-switch-btn').prop('disabled', false);
					button.html(originalHtml);
				}
			},
			error: function() {
				alert('An unknown error occurred while switching accounts.');
				// On error, re-enable buttons and restore the original content
				$('.account-switch-btn').prop('disabled', false);
				button.html(originalHtml);
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
			if (imagePreviewModal) imagePreviewModal.showModal();
		} else if (mimeType === 'application/pdf') {
			$('#pdf-preview-content').attr('src', previewUrl);
			if (pdfPreviewModal) pdfPreviewModal.showModal();
		}
	});
	
	// --- Transcription Logic ---
	const recordingModal = document.getElementById('recordingModal');
	const transcriptionResultModal = document.getElementById('transcriptionResultModal');
	const transcribeFileInput = $('#transcribe-file-input');
	let mediaRecorder;
	let audioChunks = [];
	let recordingTimerInterval;
	
	function handleTranscription(formData) {
		const uploadBtn = $('#transcribe-upload-btn');
		const recordBtn = $('#transcribe-record-btn');
		const originalUploadText = uploadBtn.html();
		const originalRecordText = recordBtn.html();
		
		// Show loading state on both buttons
		uploadBtn.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Processing...');
		recordBtn.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Processing...');
		
		$.ajax({
			url: '/api/transcribe/upload',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$('#transcription-output').val(response.text);
					if (transcribeModal) transcribeModal.close();
					if (recordingModal) recordingModal.close();
					if (transcriptionResultModal) transcriptionResultModal.showModal();
				} else {
					alert('Transcription failed: ' + (response.error || 'An unknown error occurred.'));
				}
			},
			error: function (jqXHR) {
				const errorMsg = jqXHR.responseJSON?.error || 'An error occurred during transcription.';
				alert('Error: ' + errorMsg);
			},
			complete: function () {
				uploadBtn.prop('disabled', false).html(originalUploadText);
				recordBtn.prop('disabled', false).html(originalRecordText);
			}
		});
	}
	
	// 1. Upload from file
	$('#transcribe-upload-btn').on('click', function () {
		transcribeFileInput.click();
	});
	
	transcribeFileInput.on('change', function () {
		const file = this.files[0];
		if (!file) return;
		
		const formData = new FormData();
		formData.append('file', file);
		formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
		formData.append('language', $('#transcribeInputLanguage').val());
		
		handleTranscription(formData);
		
		// Reset file input for next use
		$(this).val('');
	});
	
	// 2. Record from device
	$('#transcribe-record-btn').on('click', async function () {
		if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
			alert('Your browser does not support audio recording.');
			return;
		}
		
		try {
			const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
			if (transcribeModal) transcribeModal.close();
			if (recordingModal) recordingModal.showModal();
			
			mediaRecorder = new MediaRecorder(stream);
			audioChunks = [];
			
			mediaRecorder.ondataavailable = event => {
				audioChunks.push(event.data);
			};
			
			mediaRecorder.onstop = () => {
				const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
				const audioFile = new File([audioBlob], "recording.webm", { type: 'audio/webm' });
				
				const formData = new FormData();
				formData.append('file', audioFile);
				formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
				formData.append('language', $('#transcribeInputLanguage').val());
				
				handleTranscription(formData);
				
				// Clean up stream
				stream.getTracks().forEach(track => track.stop());
			};
			
			mediaRecorder.start();
			startRecordingTimer();
			
		} catch (err) {
			console.error("Error accessing microphone:", err);
			alert('Could not access microphone. Please check your browser permissions.');
		}
	});
	
	function startRecordingTimer() {
		let seconds = 0;
		const timerElement = $('#recording-timer');
		timerElement.text('00:00');
		recordingTimerInterval = setInterval(() => {
			seconds++;
			const min = Math.floor(seconds / 60).toString().padStart(2, '0');
			const sec = (seconds % 60).toString().padStart(2, '0');
			timerElement.text(`${min}:${sec}`);
		}, 1000);
	}
	
	function stopRecordingTimer() {
		clearInterval(recordingTimerInterval);
	}
	
	$('#stop-recording-btn').on('click', function () {
		if (mediaRecorder && mediaRecorder.state === 'recording') {
			mediaRecorder.stop();
			stopRecordingTimer();
		}
	});
	
	$('#cancel-recording-btn').on('click', function () {
		if (mediaRecorder && mediaRecorder.state === 'recording') {
			// Don't trigger onstop, just stop the tracks and close
			mediaRecorder.stream.getTracks().forEach(track => track.stop());
			mediaRecorder = null;
			stopRecordingTimer();
		}
		if (recordingModal) recordingModal.close();
		if (transcribeModal) transcribeModal.showModal(); // Re-open original modal
	});
	
	// 3. Result Modal Logic
	$('#copy-transcription-btn').on('click', function () {
		const textArea = $('#transcription-output');
		textArea.select();
		document.execCommand('copy');
		const button = $(this);
		const originalHtml = button.html();
		button.html('<i class="bi bi-check-lg"></i> Copied!');
		setTimeout(() => {
			button.html(originalHtml);
		}, 2000);
	});
});
