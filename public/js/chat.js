// public/js/chat.js:

$(document).ready(function () {
	const chatHistoryArea = $('#chat-history-area');
	const chatInputForm = $('#chat-input-form');
	const messageInputField = $('#message-input-field');
	const sendMessageButton = $('#send-message-button');
	const chatHeaderIdInput = $('#chat_header_id');
	const chatLoader = $('#chat-loader');
	const chatTitleDisplay = $('#chat-title-display');
	const sidebarMenu = $('.sidebar .menu'); // MODIFIED: Target the DaisyUI menu UL
	
	let currentAudio = null; // Variable to hold the current Audio object
	let currentReadAloudButton = null; // Variable to hold the button associated with the current audio
	
	// NOTE: The model selector dropdown logic has been moved to the global ui.js
	// to be shared across pages (like Dashboard and Chat).
	
	/**
	 * Scrolls the chat history to the bottom.
	 */
	function scrollToBottom() {
		chatHistoryArea.scrollTop(chatHistoryArea[0].scrollHeight);
	}
	
	/**
	 * Adds a message bubble to the chat interface using DaisyUI chat components.
	 * @param {string} role - 'user' or 'assistant'.
	 * @param {string} content - The message text.
	 * @param {string} messageId - The unique ID for the message element.
	 * @param {boolean} [canDelete=false] - If the message can be deleted.
	 * @param {Array} [files=[]] - An array of attached file objects.
	 */
	function addMessageBubble(role, content, messageId, canDelete = false, files = []) {
		const isUser = role === 'user';
		const escapedContentHtml = $('<div>').text(content).html().replace(/\n/g, '<br>');
		const now = new Date();
		const timeString = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
		
		// MODIFIED: Generate HTML for attached files using DaisyUI badges
		let filesHtml = '';
		if (files && files.length > 0) {
			filesHtml += '<div class="flex flex-wrap gap-2 mb-2">';
			files.forEach(file => {
				const safeFileName = $('<div>').text(file.original_filename).html();
				const truncatedName = safeFileName.length > 25 ? safeFileName.substring(0, 22) + '...' : safeFileName;
				filesHtml += `
                <a href="/api/files/${file.id}/download" class="badge badge-outline" title="Download ${safeFileName}">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>
                    ${truncatedName}
                </a>`;
			});
			filesHtml += '</div>';
		}
		
		// MODIFIED: Delete button with DaisyUI and Tailwind classes
		const deleteButtonHtml = (isUser && canDelete)
			? `<button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn" title="Delete pair" data-message-id="${messageId}">
                   <i class="bi bi-trash3-fill"></i>
               </button>`
			: '';
		
		// MODIFIED: Action buttons for assistant messages with DaisyUI classes
		const actionButtonsHtml = (role === 'assistant')
			? `<div class="chat-footer opacity-50">
                   <button class="btn btn-ghost btn-xs copy-btn" title="Copy text" data-message-id="${messageId}">
                       <i class="bi bi-clipboard"></i>
                   </button>
                   <button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud" data-message-id="${messageId}">
                       <i class="bi bi-play-circle"></i>
                       <span class="loading loading-spinner loading-xs" style="display: none;"></span>
                   </button>
               </div>`
			: '';
		
		// MODIFIED: The entire bubble structure uses the DaisyUI 'chat' component
		const bubbleHtml = `
            <div class="chat ${isUser ? 'chat-end' : 'chat-start'}" id="message-${messageId}" data-message-content="${escape(content)}">
                <div class="chat-bubble ${isUser ? 'chat-bubble-primary' : ''} relative">
                    ${filesHtml}
                    ${escapedContentHtml}
                    ${deleteButtonHtml}
                </div>
                <div class="chat-footer opacity-50">
                    <time class="text-xs">${timeString}</time>
                </div>
                ${actionButtonsHtml}
            </div>`;
		
		chatHistoryArea.append(bubbleHtml);
	}
	
	/**
	 * Enables or disables the chat input field and send button.
	 * @param {boolean} enabled - True to enable, false to disable.
	 */
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
	
	/**
	 * Auto-resizes the textarea height based on its content.
	 */
	function autoResizeTextarea() {
		messageInputField.css('height', 'auto'); // Reset height
		let scrollHeight = messageInputField[0].scrollHeight;
		messageInputField.css('height', scrollHeight + 'px');
		
		// Limit max height (e.g., 7 rows equivalent)
		let maxHeight = parseFloat(messageInputField.css('line-height')) * 7;
		if (scrollHeight > maxHeight) {
			messageInputField.css('height', maxHeight + 'px');
			messageInputField.css('overflow-y', 'auto');
		} else {
			messageInputField.css('overflow-y', 'hidden');
		}
	}
	
	messageInputField.on('input', autoResizeTextarea);
	autoResizeTextarea(); // Initial resize
	
	// --- Handle Form Submission (Send Message) ---
	chatInputForm.on('submit', function (e) {
		e.preventDefault();
		$('#empty-conversation').remove();
		
		const message = messageInputField.val().trim();
		const chatHeaderId = chatHeaderIdInput.val();
		const selectedModel = localStorage.getItem('selectedLlmModel') || 'openai/gpt-4o-mini';
		const selectedTone = localStorage.getItem('selectedPersonalityTone') || 'professional';
		const attachedFileIds = [...window.BexApp.attachedFiles.keys()];
		
		if (!message && attachedFileIds.length === 0) {
			return;
		};
		
		setInputEnabled(false);
		
		// Optimistically add user message
		const tempUserMessageId = 'temp-user-' + Date.now();
		addMessageBubble('user', message, tempUserMessageId, false);
		scrollToBottom();
		messageInputField.val('');
		autoResizeTextarea();
		
		$.ajax({
			url: '/api/chat',
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				message: message,
				chat_header_id: chatHeaderId || null,
				llm_model: selectedModel,
				personality_tone: selectedTone,
				attached_files: attachedFileIds
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					// Replace temporary user message with confirmed one
					$('#message-' + tempUserMessageId).remove();
					addMessageBubble(data.user_message.role, data.user_message.content, data.user_message.id, data.user_message.can_delete, data.user_message.files);
					
					// Add assistant message
					addMessageBubble(data.assistant_message.role, data.assistant_message.content, data.assistant_message.id, data.assistant_message.can_delete);
					
					// Update chat header ID and URL if it was a new chat
					if (data.is_new_chat && data.chat_header_id) {
						chatHeaderIdInput.val(data.chat_header_id);
						const newUrl = '/chat/' + data.chat_header_id;
						history.pushState({chatId: data.chat_header_id}, '', newUrl);
						
						// Add new chat to sidebar menu
						const newTitle = data.updated_title || 'Chat ' + data.chat_header_id;
						// MODIFIED: Create new menu item for DaisyUI menu
						const newLinkHtml = `
                         <li>
                            <a href="${newUrl}"
                               id="chat-link-${data.chat_header_id}"
                               title="${newTitle}"
                               class="active justify-between">
                                <span class="truncate">${newTitle.substring(0, 25)}</span>
                                <button class="btn btn-ghost btn-xs btn-circle delete-chat-btn" data-chat-id="${data.chat_header_id}">
                                    <i class="bi bi-trash text-error"></i>
                                </button>
                            </a>
                         </li>`;
						// Remove 'active' from other links
						sidebarMenu.find('a').removeClass('active');
						// Prepend the new link
						sidebarMenu.prepend(newLinkHtml);
						// Remove "no history" message if present
						sidebarMenu.find('.text-base-content\\/60').parent().remove();
					}
					
					// Update title if it changed
					if (data.updated_title) {
						chatTitleDisplay.text(data.updated_title.substring(0, 50));
						const sidebarLink = $('#chat-link-' + data.chat_header_id).find('span');
						if (sidebarLink.length) {
							sidebarLink.text(data.updated_title.substring(0, 25));
							$('#chat-link-' + data.chat_header_id).attr('title', data.updated_title);
						}
					}
					
					scrollToBottom();
				} else {
					// Handle backend error
					console.error("Error from server:", data.error);
					// MODIFIED: Use DaisyUI error class for visual feedback
					$('#message-' + tempUserMessageId).find('.chat-bubble').addClass('chat-bubble-error');
					alert(data.error || 'An error occurred.');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error("AJAX Error:", textStatus, errorThrown);
				// MODIFIED: Use DaisyUI error class for visual feedback
				$('#message-' + tempUserMessageId).find('.chat-bubble').addClass('chat-bubble-error');
				alert('Could not send message. Please check your connection and try again.');
			},
			complete: function () {
				setInputEnabled(true);
				// Clear attached files after sending
				window.BexApp.attachedFiles.clear();
				window.BexApp.renderFilePills();
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
	chatHistoryArea.on('click', '.delete-message-btn', function () {
		// MODIFIED: Use robust selectors for the new chat structure
		const userMessageBubble = $(this).closest('.chat');
		const userMessageId = $(this).data('message-id');
		// Find the next assistant message bubble that follows the user bubble
		const assistantMessageBubble = userMessageBubble.nextAll('.chat-start').first();
		
		if (!userMessageId) {
			console.error("Could not find user message ID for deletion.");
			return;
		}
		
		if (!confirm('Are you sure you want to delete this message and its response?')) {
			return;
		}
		
		$.ajax({
			url: `/api/chat/messages/${userMessageId}`,
			method: 'DELETE',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					userMessageBubble.fadeOut(300, function () { $(this).remove(); });
					// Ensure we're removing the correct assistant bubble
					if (data.deleted_assistant_id && assistantMessageBubble.attr('id') === 'message-' + data.deleted_assistant_id) {
						assistantMessageBubble.fadeOut(300, function () { $(this).remove(); });
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
	
	
	// --- Handle Copy Button ---
	chatHistoryArea.on('click', '.copy-btn', function () {
		const button = $(this);
		const messageContent = unescape(button.closest('.chat').data('message-content'));
		
		navigator.clipboard.writeText(messageContent).then(() => {
			const originalIcon = button.html();
			button.html('<i class="bi bi-check-lg text-success"></i>'); // Show checkmark
			setTimeout(() => {
				button.html(originalIcon);
			}, 1500);
		}).catch(err => {
			console.error('Failed to copy text: ', err);
			alert('Failed to copy text.');
		});
	});
	
	// --- Handle Read Aloud Button ---
	// NOTE: The core logic for audio playback is unchanged.
	chatHistoryArea.on('click', '.read-aloud-btn', function () {
		const button = $(this);
		const messageContent = unescape(button.closest('.chat').data('message-content'));
		
		if (currentAudio && currentReadAloudButton && currentReadAloudButton.is(button)) {
			stopAudio();
			return;
		}
		
		if (currentAudio) {
			stopAudio();
		}
		
		setReadAloudLoading(button, true);
		currentReadAloudButton = button;
		
		$.ajax({
			url: '/api/chat/tts',
			method: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content'),
				message_text: messageContent,
			},
			dataType: 'json',
			success: function (data) {
				if (data.success && data.fileUrl) {
					playAudio(data.fileUrl, button);
				} else {
					alert(data.error || 'Could not generate audio.');
					setReadAloudLoading(button, false);
					currentReadAloudButton = null;
				}
			},
			error: function () {
				alert('Could not request audio generation. Please try again.');
				setReadAloudLoading(button, false);
				currentReadAloudButton = null;
			}
		});
	});
	
	// --- Helper functions for Audio ---
	function playAudio(url, button) {
		setReadAloudLoading(button, false);
		currentAudio = new Audio(url);
		
		currentAudio.oncanplaythrough = () => {
			currentAudio.play();
			button.find('i').removeClass('bi-play-circle').addClass('bi-pause-circle-fill');
		};
		currentAudio.onended = () => {
			stopAudio();
		};
		currentAudio.onerror = () => {
			alert('Error playing the generated audio.');
			stopAudio();
		};
	}
	
	function stopAudio() {
		if (currentAudio) {
			currentAudio.pause();
			currentAudio = null;
		}
		if (currentReadAloudButton) {
			resetReadAloudButton(currentReadAloudButton);
			currentReadAloudButton = null;
		}
	}
	
	function setReadAloudLoading(button, isLoading) {
		const spinner = button.find('.loading-spinner');
		const icon = button.find('i');
		if (isLoading) {
			button.prop('disabled', true);
			spinner.show();
			icon.hide();
		} else {
			button.prop('disabled', false);
			spinner.hide();
			icon.show();
		}
	}
	
	function resetReadAloudButton(button) {
		button.find('i').removeClass('bi-pause-circle-fill').addClass('bi-play-circle');
		setReadAloudLoading(button, false);
	}
	// --- End Audio Helper Functions ---
	
	
	// --- Initial Page Load ---
	if (chatHistoryArea.children('.chat').length > 0) {
		scrollToBottom();
	}
	
	messageInputField.focus();
	autoResizeTextarea();
	
}); // End document ready
