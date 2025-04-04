$(document).ready(function () {
	const chatHistoryArea = $('#chat-history-area');
	const chatInputForm = $('#chat-input-form');
	const messageInputField = $('#message-input-field');
	const sendMessageButton = $('#send-message-button');
	const chatHeaderIdInput = $('#chat_header_id');
	const chatLoader = $('#chat-loader');
	const chatTitleDisplay = $('#chat-title-display');
	const sidebarNav = $('.sidebar .nav');
	
	let currentAudio = null; // Variable to hold the current Audio object
	let currentReadAloudButton = null; // Variable to hold the button associated with the current audio
	
	// --- LLM Model Dropdown Selection Handling (Copied from app.js) ---
	const modeDropdownButton = $('#modeDropdownButton');
	const modeDropdownMenu = modeDropdownButton.next('.dropdown-menu');
	const selectedModelNameSpan = $('#selected-model-name'); // Target the span inside the button
	const defaultModelId = 'openai/gpt-4o-mini'; // Default model
	
	function applySelectedModel(modelId) {
		const selectedItem = modeDropdownMenu.find(`.dropdown-item[data-model-id="${modelId}"]`);
		let displayName = 'Smart Mode'; // Default display name
		
		// Remove active state and checkmark from all items
		modeDropdownMenu.find('.dropdown-item').removeClass('active').find('i.bi-check').remove();
		
		if (selectedItem.length) {
			displayName = selectedItem.data('display-name') || selectedItem.text().trim();
			// Add active state and checkmark to the selected item
			selectedItem.addClass('active').prepend('<i class="bi bi-check me-2"></i>');
			console.log('Applied model:', modelId, 'Display:', displayName);
		} else {
			// If the saved model ID is invalid, fallback to the default visually
			const defaultItem = modeDropdownMenu.find(`.dropdown-item[data-model-id="${defaultModelId}"]`);
			if (defaultItem.length) {
				displayName = defaultItem.data('display-name') || defaultItem.text().trim();
				defaultItem.addClass('active').prepend('<i class="bi bi-check me-2"></i>');
				console.log('Applied default model (fallback):', defaultModelId, 'Display:', displayName);
			} else {
				console.error("Default model item not found in dropdown!");
			}
		}
		// Update button text
		if (selectedModelNameSpan.length) {
			selectedModelNameSpan.text(displayName);
		} else {
			modeDropdownButton.text(displayName); // Fallback if span not found
		}
	}
	
	// Event listener for dropdown item clicks
	modeDropdownMenu.on('click', '.dropdown-item', function (e) {
		e.preventDefault();
		const selectedModelId = $(this).data('model-id');
		if (selectedModelId) {
			localStorage.setItem('selectedLlmModel', selectedModelId);
			applySelectedModel(selectedModelId);
			console.log('Model selection saved:', selectedModelId);
		}
	});
	
	// Apply saved theme on load or default
	const savedModel = localStorage.getItem('selectedLlmModel');
	applySelectedModel(savedModel || defaultModelId);
	
	function checkAndSubmitInitialPrompt() {
		const initialMessage = messageInputField.val().trim();
		if (initialMessage) {
			console.log('Initial prompt detected, submitting...');
			chatInputForm.submit(); // Trigger form submission
		}
	}
	
	function scrollToBottom() {
		chatHistoryArea.scrollTop(chatHistoryArea[0].scrollHeight);
	}
	
	// Function to add a message bubble to the chat
	function addMessageBubble(role, content, messageId, canDelete = false) {
		const escapedContentHtml = $('<div>').text(content).html().replace(/\n/g, '<br>');
		const now = new Date();
		const timeString = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
		
		const deleteButtonHtml = (role === 'user' && canDelete)
			? `<button class="delete-message-btn" title="Delete pair" data-message-id="${messageId}"> <i class="bi bi-trash3-fill"></i> </button>`
			: '';
		
		// --- Add Action Buttons for Assistant ---
		const actionButtonsHtml = (role === 'assistant')
			? `<div class="message-actions">
                   <button class="btn btn-sm btn-outline-secondary copy-btn" title="Copy text" data-message-id="${messageId}">
                       <i class="bi bi-clipboard"></i>
                   </button>
                   <button class="btn btn-sm btn-outline-secondary read-aloud-btn" title="Read aloud" data-message-id="${messageId}">
                       <i class="bi bi-play-circle"></i>
                       <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                   </button>
               </div>`
			: '';
		// --- End Action Buttons ---
		
		// Add data-message-content to the main bubble for easier access later
		const bubbleHtml = `
            <div class="message-bubble ${role}" id="message-${messageId}" data-message-content="${escape(content)}"> ${/* Store escaped content */''}
                ${escapedContentHtml}
                ${deleteButtonHtml}
                <div class="message-meta">${timeString}</div>
                ${actionButtonsHtml} ${/* Add assistant action buttons */''}
            </div>`;
		
		chatHistoryArea.append(bubbleHtml);
	}
	
	// Function to enable/disable input
	function setInputEnabled(enabled) {
		messageInputField.prop('disabled', !enabled);
		sendMessageButton.prop('disabled', !enabled);
		if (enabled) {
			messageInputField.focus();
			chatLoader.hide();
		} else {
			chatLoader.show();
		}
	}
	
	// --- Auto-resize Textarea ---
	function autoResizeTextarea() {
		messageInputField.css('height', 'auto'); // Reset height
		let scrollHeight = messageInputField[0].scrollHeight;
		messageInputField.css('height', scrollHeight + 'px');
		
		// Limit max height (e.g., 5 rows equivalent)
		let maxHeight = parseFloat(messageInputField.css('line-height')) * 7;
		if (scrollHeight > maxHeight) {
			messageInputField.css('height', maxHeight + 'px');
			messageInputField.css('overflow-y', 'auto'); // Add scroll if max height reached
		} else {
			messageInputField.css('overflow-y', 'hidden'); // Hide scroll if below max height
		}
	}
	
	messageInputField.on('input', autoResizeTextarea);
	autoResizeTextarea(); // Initial resize
	
	// --- Handle Form Submission (Send Message) ---
	chatInputForm.on('submit', function (e) {
		e.preventDefault();
		$('#empty-conversation').remove();

		const message = messageInputField.val().trim();
		const chatHeaderId = chatHeaderIdInput.val(); // Get current chat ID
		const selectedModel = localStorage.getItem('selectedLlmModel') || defaultModelId;
		
		if (!message) return; // Don't send empty messages
		
		setInputEnabled(false); // Disable input while processing
		
		// Optimistically add user message
		const tempUserMessageId = 'temp-user-' + Date.now();
		addMessageBubble('user', message, tempUserMessageId, false); // Cannot delete until saved
		scrollToBottom();
		messageInputField.val(''); // Clear input field immediately
		autoResizeTextarea(); // Reset textarea height
		
		$.ajax({
			url: '/api/chat', // Use the named route if preferred: {{ route('api.chat.store') }} - needs JS variables setup
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
				message: message,
				chat_header_id: chatHeaderId || null, // Send null if no ID (new chat)
				llm_model: selectedModel
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					// Remove temporary user message
					$('#message-' + tempUserMessageId).remove();
					
					// Add confirmed user message (with delete button)
					addMessageBubble(
						data.user_message.role,
						data.user_message.content,
						data.user_message.id,
						data.user_message.can_delete
					);
					
					// Add assistant message
					addMessageBubble(
						data.assistant_message.role,
						data.assistant_message.content,
						data.assistant_message.id,
						data.assistant_message.can_delete
					);
					
					// Update chat header ID if it was a new chat
					if (data.is_new_chat && data.chat_header_id) {
						chatHeaderIdInput.val(data.chat_header_id);
						// Update browser URL without reloading
						const newUrl = '/chat/' + data.chat_header_id;
						history.pushState({chatId: data.chat_header_id}, '', newUrl);
						
						// Add new chat to sidebar
						const newTitle = data.updated_title || 'Chat ' + data.chat_header_id;
						const newLinkHtml = `
                         <a class="nav-link py-1 ps-3 pe-2 d-flex justify-content-between align-items-center active fw-bold"
                            href="${newUrl}"
                            id="chat-link-${data.chat_header_id}"
                            title="${newTitle}">
                             <span class="text-truncate" style="max-width: 180px;">${newTitle.substring(0, 25)}</span>
                         </a>`;
						// Remove 'active' from other links
						sidebarNav.find('a').removeClass('active fw-bold');
						// Prepend the new link
						sidebarNav.prepend(newLinkHtml);
						// Remove "no history" message if present
						sidebarNav.find('.text-muted.small').remove();
					}
					
					// Update title if it changed
					if (data.updated_title) {
						chatTitleDisplay.text(data.updated_title.substring(0, 50));
						// Update the title in the sidebar link as well
						const sidebarLink = $('#chat-link-' + data.chat_header_id).find('span');
						if (sidebarLink.length) {
							sidebarLink.text(data.updated_title.substring(0, 25));
							$('#chat-link-' + data.chat_header_id).attr('title', data.updated_title);
						}
					}
					
					scrollToBottom();
				} else {
					// Handle backend error reported in success response
					console.error("Error from server:", data.error);
					$('#message-' + tempUserMessageId).addClass('bg-danger text-white').append('<br><small>Failed to send</small>');
					alert(data.error || 'An error occurred.');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error("AJAX Error:", textStatus, errorThrown);
				$('#message-' + tempUserMessageId).addClass('bg-danger text-white').append('<br><small>Failed to send</small>');
				alert('Could not send message. Please check your connection and try again.');
			},
			complete: function () {
				setInputEnabled(true); // Re-enable input
			}
		});
	});
	
	// --- Handle Enter Key in Textarea (Submit, allow Shift+Enter for newline) ---
	messageInputField.on('keydown', function (e) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault(); // Prevent default newline
			chatInputForm.submit(); // Trigger form submission
		}
	});
	
	
	// --- Handle Delete Message Pair ---
	// Use event delegation for dynamically added buttons
	chatHistoryArea.on('click', '.delete-message-btn', function () {
		const userMessageId = $(this).data('message-id');
		const userMessageBubble = $('#message-' + userMessageId);
		const assistantMessageBubble = userMessageBubble.next('.message-bubble.assistant'); // Find the *next* sibling that is an assistant bubble
		
		if (!userMessageId) {
			console.error("Could not find user message ID for deletion.");
			return;
		}
		
		if (!confirm('Are you sure you want to delete this message and its response?')) {
			return;
		}
		
		$.ajax({
			url: `/api/chat/messages/${userMessageId}`, // Use template literal for URL
			method: 'DELETE',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					// Remove user message bubble
					userMessageBubble.fadeOut(300, function () {
						$(this).remove();
					});
					// Remove assistant message bubble if found and deleted
					if (data.deleted_assistant_id && assistantMessageBubble.attr('id') === 'message-' + data.deleted_assistant_id) {
						assistantMessageBubble.fadeOut(300, function () {
							$(this).remove();
						});
					} else {
						console.warn("Could not visually confirm the correct assistant message for removal, but backend reported success.");
						// Attempt removal by ID anyway if the sibling logic failed but backend gave an ID
						if (data.deleted_assistant_id) {
							$('#message-' + data.deleted_assistant_id).fadeOut(300, function () {
								$(this).remove();
							});
						}
					}
				} else {
					alert(data.error || 'Could not delete messages.');
					console.error("Deletion error:", data.error);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				alert('An error occurred while trying to delete messages.');
				console.error("AJAX Deletion Error:", textStatus, errorThrown);
			}
		});
	});
	
	
	// --- NEW: Handle Copy Button ---
	chatHistoryArea.on('click', '.copy-btn', function () {
		const button = $(this);
		// Get content from the parent bubble's data attribute
		const messageContent = unescape(button.closest('.message-bubble').data('message-content'));
		
		navigator.clipboard.writeText(messageContent).then(() => {
			// Success feedback
			const originalIcon = button.html();
			button.html('<i class="bi bi-check-lg"></i>'); // Show checkmark
			setTimeout(() => {
				button.html(originalIcon); // Restore original icon
			}, 1500); // Restore after 1.5 seconds
		}).catch(err => {
			console.error('Failed to copy text: ', err);
			alert('Failed to copy text.');
		});
	});
	
	// --- NEW: Handle Read Aloud Button ---
	chatHistoryArea.on('click', '.read-aloud-btn', function () {
		const button = $(this);
		const messageContent = unescape(button.closest('.message-bubble').data('message-content'));
		const messageId = button.data('message-id'); // Although not strictly needed for TTS here
		
		// If this button is already playing, stop it
		if (currentAudio && currentReadAloudButton && currentReadAloudButton.is(button)) {
			stopAudio();
			return; // Exit
		}
		
		// If another audio is playing, stop it first
		if (currentAudio) {
			stopAudio();
		}
		
		// Show loading state
		setReadAloudLoading(button, true);
		currentReadAloudButton = button; // Store the current button
		
		$.ajax({
			url: '/api/chat/tts', // The new API endpoint
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				message_text: messageContent,
				// You could add voice preference here if needed later
				// voice: 'alloy'
			},
			dataType: 'json',
			success: function (data) {
				if (data.success && data.fileUrl) {
					playAudio(data.fileUrl, button);
				} else {
					console.error('TTS generation failed:', data.error);
					alert(data.error || 'Could not generate audio for this message.');
					setReadAloudLoading(button, false); // Remove loading state on error
					resetReadAloudButton(button); // Reset just in case
					currentReadAloudButton = null;
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error('AJAX Error during TTS:', textStatus, errorThrown);
				alert('Could not request audio generation. Please try again.');
				setReadAloudLoading(button, false); // Remove loading state on error
				resetReadAloudButton(button); // Reset just in case
				currentReadAloudButton = null;
			}
			// Note: 'complete' isn't used here as success/error handle loading state removal
		});
	});
	
	// --- NEW: Helper functions for Audio ---
	function playAudio(url, button) {
		// Ensure loading state is removed before playing
		setReadAloudLoading(button, false);
		
		currentAudio = new Audio(url);
		
		currentAudio.oncanplaythrough = () => {
			console.log("Audio ready to play:", url);
			currentAudio.play();
			button.addClass('playing').attr('title', 'Pause'); // Update button state
			button.find('i').removeClass('bi-play-circle').addClass('bi-pause-circle-fill');
		};
		
		currentAudio.onended = () => {
			console.log("Audio finished playing");
			stopAudio(); // Clean up and reset button
		};
		
		currentAudio.onerror = (e) => {
			console.error('Error playing audio:', e);
			alert('Error playing the generated audio.');
			stopAudio(); // Clean up even on error
		};
		
		// Add a timeout in case 'oncanplaythrough' never fires (network issue?)
		let playTimeout = setTimeout(() => {
			if (currentAudio && currentAudio.paused) { // Check if not already playing/played
				console.warn("Audio 'canplaythrough' event timed out. Attempting play anyway.");
				currentAudio.play().catch(e => { // Need catch here too
					console.error('Force play attempt failed:', e);
					alert('Could not start audio playback.');
					stopAudio();
				});
			}
		}, 5000); // 5 second timeout
		
		// Clear timeout if event fires normally
		currentAudio.addEventListener('canplaythrough', () => clearTimeout(playTimeout), { once: true });
		currentAudio.addEventListener('ended', () => clearTimeout(playTimeout), { once: true });
		currentAudio.addEventListener('error', () => clearTimeout(playTimeout), { once: true });
		
		
	}
	
	function stopAudio() {
		if (currentAudio) {
			currentAudio.pause();
			currentAudio.currentTime = 0; // Reset playback position
			currentAudio = null;
		}
		if (currentReadAloudButton) {
			resetReadAloudButton(currentReadAloudButton);
			currentReadAloudButton = null;
		}
	}
	
	function setReadAloudLoading(button, isLoading) {
		if (isLoading) {
			button.prop('disabled', true).addClass('loading');
			button.find('.spinner-border').show();
			button.find('i').hide();
		} else {
			button.prop('disabled', false).removeClass('loading');
			button.find('.spinner-border').hide();
			button.find('i').show();
		}
	}
	
	function resetReadAloudButton(button) {
		button.removeClass('playing').attr('title', 'Read aloud');
		button.find('i').removeClass('bi-pause-circle-fill').addClass('bi-play-circle');
		// Ensure loading state is also removed
		setReadAloudLoading(button, false);
	}
	// --- End Audio Helper Functions ---
	
	
	// Initial scroll to bottom if there are messages
	if (chatHistoryArea.children('.message-bubble').length > 0) {
		scrollToBottom();
	}
	
	messageInputField.focus();
	checkAndSubmitInitialPrompt();
	autoResizeTextarea();
	
}); // End document ready
