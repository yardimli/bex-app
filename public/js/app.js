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

	function handleListItemClick(modalId, detailsSelector, placeholderText) {
		$(modalId + ' .list-group-item').on('click', function (e) {
			e.preventDefault();
			$(modalId + ' .list-group-item').removeClass('active');
			$(this).addClass('active');
			const itemId = $(this).data('id');
			const itemTitle = $(this).find('strong').text() || $(this).text();
			const detailsPane = $(modalId + ' ' + detailsSelector);
			detailsPane.html(` <div class="text-center"> <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"> <span class="visually-hidden">Loading...</span> </div> <p>Loading details for: <br><strong>${itemTitle}</strong></p> <small class="text-muted">(ID: ${itemId || 'N/A'})</small> </div> `);
			setTimeout(() => {
				detailsPane.html(` <h4>Details for ${itemTitle}</h4> <p>This is where the actual content, player, or preview for "${itemTitle}" would be loaded via AJAX.</p> <p>Item ID: ${itemId || 'N/A'}</p> ${modalId === '#myRecordingsModal' ? '<button class="btn btn-sm btn-primary mt-2"><i class="bi bi-play-fill"></i> Play Recording</button>' : ''} ${modalId === '#teamFilesModal' ? '<button class="btn btn-sm btn-info mt-2"><i class="bi bi-eye-fill"></i> Preview File</button>' : ''} `);
			}, 500);
			console.log(`Item clicked in ${modalId}: ID=${itemId}, Title=${itemTitle}`);
		});
	}

	handleListItemClick('#recentMeetingsModal', '.details-pane', 'Select a meeting to view details');
	handleListItemClick('#myNotesModal', '.details-pane', 'Select a note to view details');
	handleListItemClick('#myRecordingsModal', '.details-pane', 'Select a recording to play');
	handleListItemClick('#teamFilesModal', '.details-pane', 'Select a file to view details or preview');

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

		if (data.text_key) {
			// Pass the key and a preview (or a flag to fetch full text)
			// The chat page will use the key to get the full text from session
			redirectUrl = chatUrl + '?summarize_key=' + encodeURIComponent(data.text_key) +
				'&prompt_prefix=' + encodeURIComponent(data.prompt_prefix || '');
		} else if (data.full_text_for_prompt) {
			const promptText = (data.prompt_prefix || '') + data.full_text_for_prompt;
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
						text_key: response.text_key, // Will be undefined if not set
						full_text_for_prompt: response.full_text_for_prompt, // Will be undefined if not set
						prompt_prefix: response.prompt_prefix
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
				} else if (jqXHR.responseText) { try { const err = JSON.parse(jqXHR.responseText); if (err.message) errorMsg = err.message; } catch (e) {} }
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
		setTimeout(() => { window.location.href = redirectUrl; }, 150);
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
		if (file.size > 1 * 1024 * 1024) { // 10MB limit
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
						text_key: response.text_key,
						full_text_for_prompt: response.full_text_for_prompt,
						prompt_prefix: response.prompt_prefix
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
				if (jqXHR.responseJSON && jqXHR.responseJSON.error) { errorMsg = jqXHR.responseJSON.error; } else if (jqXHR.responseText) { try { const err = JSON.parse(jqXHR.responseText); if (err.message) errorMsg = err.message; } catch (e) {} }
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
		// const selectedText = $(this).text().trim();
		// $('#modeDropdownButton').text(selectedText);
		// $('#modeDropdownButton + .dropdown-menu .dropdown-item').removeClass('active').find('i.bi-check').remove();
		// $(this).addClass('active').prepend('<i class="bi bi-check me-2"></i>');
		// console.log('Mode changed to:', selectedText);
	});

	const dashboardPromptForm = $('#dashboard-prompt-form');
	const dashboardPromptInput = $('#dashboard-prompt-input');
	if (dashboardPromptForm.length && dashboardPromptInput.length) {
		dashboardPromptForm.on('submit', function (e) {
			e.preventDefault();
			const promptText = dashboardPromptInput.val().trim();
			if (promptText) {
				const chatUrl = $(this).attr('action');
				const redirectUrl = chatUrl + '?prompt=' + encodeURIComponent(promptText);
				window.location.href = redirectUrl;
			}
		});
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
				data: {_token: $('meta[name="csrf-token"]').attr('content')},
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

});
