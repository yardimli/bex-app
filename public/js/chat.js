$(document).ready(function () {
	const chatHistoryArea = $('#chat-history-area');
	const chatInputForm = $('#chat-input-form');
	const messageInputField = $('#message-input-field');
	const sendMessageButton = $('#send-message-button');
	const chatHeaderIdInput = $('#chat_header_id');
	const chatLoader = $('#chat-loader');
	const chatTitleDisplay = $('#chat-title-display');
	const sidebarNav = $('.sidebar .nav');
	
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
		// Basic XSS protection (escape HTML, then replace newlines with <br>)
		const escapedContent = $('<div>').text(content).html().replace(/\n/g, '<br>');
		
		// --- Get current time from user's computer ---
		const now = new Date();
		const hours = now.getHours().toString().padStart(2, '0'); // Get hours (0-23) and pad with '0' if needed
		const minutes = now.getMinutes().toString().padStart(2, '0'); // Get minutes and pad with '0' if needed
		const timeString = `${hours}:${minutes}`; // Format as HH:MM
		// --- End Timestamp ---
		
		const deleteButtonHtml = canDelete ?
			`<button class="delete-message-btn" title="Delete pair" data-message-id="${messageId}">
                 <i class="bi bi-trash3-fill"></i>
             </button>` : '';
		
		// Add the message-meta div with the calculated timeString
		const bubbleHtml = `
            <div class="message-bubble ${role}" id="message-${messageId}">
                ${escapedContent}
                ${deleteButtonHtml}
                <div class="message-meta">${timeString}</div>
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
	
	// Initial scroll to bottom if there are messages
	if (chatHistoryArea.children('.message-bubble').length > 0) {
		scrollToBottom();
	}
	
	messageInputField.focus();
	checkAndSubmitInitialPrompt();
	autoResizeTextarea();
	
}); // End document ready
