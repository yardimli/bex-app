$(document).ready(function () {
    const chatHistoryArea = $('#chat-history-area');
    const chatInputForm = $('#chat-input-form');
    const messageInputField = $('#message-input-field');
    const sendMessageButton = $('#send-message-button');
    const groupChatHeaderIdInput = $('#group_chat_header_id');
    const teamIdInput = $('#team_id');
    const chatLoader = $('#chat-loader');
    const chatTitleDisplay = $('#chat-title-display');
    const sidebarMenu = $('#chat-history-list');
    const currentUserId = parseInt($('#current_user_id').val(), 10);
    const typingIndicator = $('#typing-indicator');
    // --- Start: Mention Feature Variables ---
    const mentionsDropdown = $('#mentions-dropdown');
    const mentionsList = $('#mentions-list');
    let isMentioning = false;
    let groupParticipants = chatInputForm.data('participants');

    // Add a safety check to ensure it's an array, in case the attribute is missing or malformed.
    if (!Array.isArray(groupParticipants)) {
        console.error("Participants data was not a valid array, defaulting to empty.", groupParticipants);
        groupParticipants = [];
    }

    // --- End: Mention Feature Variables ---
    let currentAudio = null;
    let currentReadAloudButton = null;

    let pollingInterval; // To hold the interval ID
    let typingTimer; // To hold the typing timeout
    const typingTimeout = 800;

    function scrollToBottom() {
        chatHistoryArea.scrollTop(chatHistoryArea[0].scrollHeight);
    }

    function addMessageBubble(role, content, messageId, user = null, files = []) {
        const isAssistant = role === 'assistant';
        const isCurrentUser = !isAssistant && user && user.id === currentUserId;
        const alignment = isAssistant ? 'chat-start' : (isCurrentUser ? 'chat-end' : 'chat-start');
        const bubbleColor = isCurrentUser ? 'chat-bubble-primary' : 'bg-white border border-base-200 text-base-content';

        let processedContent = $('<div>').text(content).html(); // Escape first
        if (typeof groupParticipants !== 'undefined' && groupParticipants.length > 0) {
            const participantNames = groupParticipants.map(p => p.name.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'));
            if (participantNames.length > 0) {
                const mentionRegex = new RegExp(`@(${participantNames.join('|')})\\b`, 'gi');
                // Use the same light green style as the blade file
                processedContent = processedContent.replace(mentionRegex, '<span class="bg-green-100 text-green-800 font-semibold rounded px-1 py-0.5">$&</span>');
            }
        }
        const escapedContentHtml = processedContent.replace(/\n/g, '<br>');

        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

        let filesHtml = '';
        if (files && files.length > 0) {
            filesHtml += '<div class="flex flex-wrap gap-2 mb-2">';
            files.forEach(file => {
                const safeFileName = $('<div>').text(file.original_filename).html();
                const truncatedName = safeFileName.length > 25 ? safeFileName.substring(0, 22) + '...' : safeFileName;
                filesHtml += ` <a href="/api/files/${file.id}/download" class="badge badge-outline" title="Download ${safeFileName}"> <i class="bi bi-file-earmark-arrow-down me-1"></i> ${truncatedName} </a>`;
            });
            filesHtml += '</div>';
        }

        const deleteButtonHtml = (isCurrentUser) ? `<button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn" title="Delete pair" data-message-id="${messageId}"> <i class="bi bi-trash3-fill"></i> </button>` : '';
        const senderName = isAssistant ? 'Bex' : (user ? user.name : 'Unknown User');
        const escapedSenderName = $('<div>').text(senderName).html();

        // --- START: NEW AVATAR LOGIC ---
        let avatarInitials = 'BX';
        let avatarColorClass = 'bg-neutral text-neutral-content';
        if (!isAssistant && user) {
            const name = user.name;
            const words = name.split(" ");
            let initials = "";
            if (words.length >= 2) {
                initials += words[0].substring(0, 1).toUpperCase();
                initials += words[words.length - 1].substring(0, 1).toUpperCase();
            } else if (name.length > 1) {
                initials = name.substring(0, 2).toUpperCase();
            } else {
                initials = "??";
            }
            avatarInitials = initials;
            const colors = ['bg-sky-500', 'bg-amber-500', 'bg-rose-500', 'bg-violet-500', 'bg-emerald-500', 'bg-red-500'];
            // Simple hash to get a consistent color from user ID
            let hash = 0;
            for (let i = 0; i < String(user.id).length; i++) {
                const char = String(user.id).charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash |= 0;
            }
            const colorIndex = Math.abs(hash) % colors.length;
            avatarColorClass = colors[colorIndex] + ' text-white';
        }
        const avatarHtml = ` <div class="chat-image avatar placeholder"> <div class="w-10 rounded-full ${avatarColorClass}"> <span class="text-lg">${avatarInitials}</span> </div> </div>`;
        // --- END: NEW AVATAR LOGIC ---

        const headerHtml = ` <div class="chat-header"> ${escapedSenderName} <time class="text-xs opacity-50 ml-1">${timeString}</time> </div>`;

        let footerHtml = '';
        if (isAssistant) {
            const assistantButtons = ` <button class="btn btn-ghost btn-xs copy-btn" title="Copy text"><i class="bi bi-clipboard"></i></button> <button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud"> <i class="bi bi-play-circle"></i> <span class="loading loading-spinner loading-xs" style="display: none;"></span> </button> `;
            footerHtml = `<div class="chat-footer opacity-50 flex items-center gap-1 mt-1">${assistantButtons}</div>`;
        }

        const bubbleHtml = ` <div class="chat ${alignment}" id="message-${messageId}" data-message-content="${escape(content)}"> ${avatarHtml} ${headerHtml} <div class="chat-bubble ${bubbleColor} relative"> ${filesHtml} ${escapedContentHtml} ${deleteButtonHtml} </div> ${footerHtml} </div>`;
        chatHistoryArea.append(bubbleHtml);
    }

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

    function autoResizeTextarea() {
        messageInputField.css('height', 'auto');
        let scrollHeight = messageInputField[0].scrollHeight;
        messageInputField.css('height', scrollHeight + 'px');
        let maxHeight = parseFloat(messageInputField.css('line-height')) * 7;
        if (scrollHeight > maxHeight) {
            messageInputField.css('height', maxHeight + 'px');
            messageInputField.css('overflow-y', 'auto');
        } else {
            messageInputField.css('overflow-y', 'hidden');
        }
    }

    // --- Start: Mention Feature Logic ---
    function updateMentionsDropdown(query) {
        if (typeof groupParticipants === 'undefined') return;

        const filteredParticipants = groupParticipants.filter(p =>
            p.name.toLowerCase().includes(query.toLowerCase())
        );
        mentionsList.empty();

        if (filteredParticipants.length > 0) {
            // Add a header to the dropdown like in the image
            mentionsList.append('<li class="menu-title">Mention someone</li>');

            filteredParticipants.forEach((p, index) => {
                // --- Avatar Generation Logic (copied and adapted for dropdown) ---
                const name = p.name;
                const words = name.split(" ");
                let initials = "";
                if (words.length >= 2) {
                    initials += words[0].substring(0, 1).toUpperCase();
                    initials += words[words.length - 1].substring(0, 1).toUpperCase();
                } else if (name.length > 1) {
                    initials = name.substring(0, 2).toUpperCase();
                } else {
                    initials = "??";
                }
                const avatarInitials = initials;
                const colors = ['bg-sky-500', 'bg-amber-500', 'bg-rose-500', 'bg-violet-500', 'bg-emerald-500', 'bg-red-500'];
                let hash = 0;
                for (let i = 0; i < String(p.id).length; i++) {
                    const char = String(p.id).charCodeAt(i);
                    hash = ((hash << 5) - hash) + char;
                    hash |= 0;
                }
                const colorIndex = Math.abs(hash) % colors.length;
                const avatarColorClass = colors[colorIndex] + ' text-white';
                // --- End Avatar Logic ---

                // Create the new list item with avatar and name
                const listItem = $(`
                    <li>
                        <a href="#" class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="w-8 p-1 rounded-full ${avatarColorClass}">
                                    <span class="text-xs">${avatarInitials}</span>
                                </div>
                            </div>
                            <span>${$('<div>').text(p.name).html()}</span>
                        </a>
                    </li>
                `);

                listItem.data('username', p.name);
                if (index === 0) {
                    // Use a different class for keyboard navigation to avoid style conflicts
                    listItem.addClass('mention-active');
                }
                mentionsList.append(listItem);
            });
            mentionsDropdown.show();
        } else {
            mentionsDropdown.hide();
        }
    }


    function selectMention(username) {
        const text = messageInputField.val();
        const cursorPos = messageInputField.prop('selectionStart');
        let startIndex = text.substring(0, cursorPos).lastIndexOf('@');

        if (startIndex === -1) {
            isMentioning = false;
            mentionsDropdown.hide();
            return;
        }

        const newText = text.substring(0, startIndex) + `@${username} ` + text.substring(cursorPos);
        messageInputField.val(newText);
        const newCursorPos = startIndex + username.length + 2;
        messageInputField[0].setSelectionRange(newCursorPos, newCursorPos);

        isMentioning = false;
        mentionsDropdown.hide();
        messageInputField.focus();
    }

    function pollForUpdates() {
        const groupChatHeaderId = groupChatHeaderIdInput.val();
        const teamId = teamIdInput.val();

        if (!groupChatHeaderId || !teamId) {
            return; // Don't poll if we're not in a chat
        }

        let lastMessageId = 0;
        const lastMessageElement = chatHistoryArea.find('.chat[id^="message-"]').last();
        if (lastMessageElement.length) {
            const idStr = lastMessageElement.attr('id');
            // Ensure we only parse numeric IDs, not temp ones
            const potentialId = parseInt(idStr.replace('message-', ''), 10);
            if (!isNaN(potentialId)) {
                lastMessageId = potentialId;
            }
        }

        $.ajax({
            url: `/api/team/${teamId}/group-chat/${groupChatHeaderId}/updates`,
            method: 'GET',
            data: { last_message_id: lastMessageId },
            dataType: 'json',
            success: function(data) {
                // Append new messages
                if (data.new_messages && data.new_messages.length > 0) {
                    let shouldScroll = (chatHistoryArea.scrollTop() + chatHistoryArea.innerHeight() + 100) >= chatHistoryArea[0].scrollHeight;

                    data.new_messages.forEach(message => {
                        // Check if message already exists to avoid duplicates
                        if ($('#message-' + message.id).length === 0) {
                            addMessageBubble(message.role, message.content, message.id, message.user, message.files);
                        }
                    });

                    if (shouldScroll) {
                        scrollToBottom();
                    }
                }

                // Update typing indicator
                if (data.typing_users && data.typing_users.length > 0) {
                    const names = data.typing_users.join(', ');
                    const verb = data.typing_users.length > 1 ? 'are' : 'is';
                    typingIndicator.text(`${names} ${verb} typing...`).show();
                } else {
                    typingIndicator.hide();
                }
            },
            error: function(jqXHR) {
                // Stop polling on auth errors or if chat is not found
                if (jqXHR.status === 403 || jqXHR.status === 404) {
                    clearInterval(pollingInterval);
                }
            }
        });
    }

    function notifyTyping() {
        const groupChatHeaderId = groupChatHeaderIdInput.val();
        const teamId = teamIdInput.val();
        if (!groupChatHeaderId || !teamId) return;

        $.ajax({
            url: `/api/team/${teamId}/group-chat/${groupChatHeaderId}/typing`,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') }
        });
    }

    messageInputField.on('input', function() {
        autoResizeTextarea();
        const input = $(this);
        const text = input.val();
        const cursorPos = input.prop('selectionStart');
        let wordStartIndex = text.lastIndexOf(' ', cursorPos - 1) + 1;
        let currentWord = text.substring(wordStartIndex, cursorPos);

        if (currentWord.startsWith('@')) {
            isMentioning = true;
            const mentionQuery = currentWord.substring(1);
            updateMentionsDropdown(mentionQuery);
        } else {
            isMentioning = false;
            mentionsDropdown.hide();
        }

        clearTimeout(typingTimer);
        typingTimer = setTimeout(notifyTyping, typingTimeout);
    });

    mentionsList.on('click', 'li', function(e) {
        e.preventDefault();
        const username = $(this).data('username');
        selectMention(username);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#chat-input-form').length) {
            isMentioning = false;
            mentionsDropdown.hide();
        }
    });
    // --- End: Mention Feature Logic ---

    autoResizeTextarea();

    chatInputForm.on('submit', function (e) {
        e.preventDefault();
        $('#empty-conversation').remove();
        const message = messageInputField.val().trim();
        const attachedFileIds = [...window.BexApp.attachedFiles.keys()];
        const attachedFilesForBubble = [];
        window.BexApp.attachedFiles.forEach((file, id) => {
            attachedFilesForBubble.push({ id: id, original_filename: file.name });
        });
        if (!message && attachedFileIds.length === 0) { return; };
        const groupChatHeaderId = groupChatHeaderIdInput.val();
        const teamId = teamIdInput.val();
        const selectedModel = localStorage.getItem('selectedLlmModel') || 'openai/gpt-4o-mini';
        setInputEnabled(false);
        const tempUserMessageId = 'temp-user-' + Date.now();
        addMessageBubble('user', message, tempUserMessageId, { id: currentUserId, name: 'You' }, false, attachedFilesForBubble);
        scrollToBottom();
        messageInputField.val('');
        autoResizeTextarea();
        window.BexApp.attachedFiles.clear();
        window.BexApp.renderFilePills();
        $.ajax({
            url: '/api/group-chat/store',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: message,
                team_id: teamId,
                group_chat_header_id: groupChatHeaderId || null,
                attached_files: attachedFileIds,
                llm_model: selectedModel,
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#message-' + tempUserMessageId).remove();
                    addMessageBubble(data.user_message.role, data.user_message.content, data.user_message.id, data.user_message.user, data.user_message.files);
                    if (data.assistant_message) {
                        addMessageBubble(data.assistant_message.role, data.assistant_message.content, data.assistant_message.id, null, []);
                    }
                    if (data.is_new_chat && data.group_chat_header_id) {
                        groupChatHeaderIdInput.val(data.group_chat_header_id);
                        const newUrl = `/team/${teamId}/group-chat/${data.group_chat_header_id}`;
                        history.pushState({ chatId: data.group_chat_header_id }, '', newUrl);
                        const newTitle = data.updated_title || 'Group Chat ' + data.group_chat_header_id;
                        const newLinkHtml = ` <li> <a href="${newUrl}" id="chat-link-${data.group_chat_header_id}" title="${newTitle}" class="active justify-between"> <span class="truncate">${newTitle.substring(0, 25)}</span> <button class="btn btn-ghost btn-xs btn-circle delete-chat-btn" data-chat-id="${data.group_chat_header_id}" data-type="group"> <i class="bi bi-trash text-error"></i> </button> </a> </li>`;
                        sidebarMenu.find('a').removeClass('active');
                        sidebarMenu.prepend(newLinkHtml);
                        sidebarMenu.find('.text-base-content\\/60').parent().remove();

                        if (!pollingInterval) {
                            pollingInterval = setInterval(pollForUpdates, 3000);
                        }
                    }
                    if (data.updated_title) {
                        chatTitleDisplay.text(data.updated_title.substring(0, 50));
                        const sidebarLink = $(`#chat-link-${data.group_chat_header_id}`).find('span');
                        if (sidebarLink.length) {
                            sidebarLink.text(data.updated_title.substring(0, 25));
                            $(`#chat-link-${data.group_chat_header_id}`).attr('title', data.updated_title);
                        }
                    }
                    scrollToBottom();
                    document.dispatchEvent(new CustomEvent('usageUpdated'));
                } else {
                    $('#message-' + tempUserMessageId).find('.chat-bubble').addClass('chat-bubble-error');
                    alert(data.error || 'An error occurred.');
                }
            },
            error: function () {
                $('#message-' + tempUserMessageId).find('.chat-bubble').addClass('chat-bubble-error');
                alert('Could not send message. Please check your connection and try again.');
            },
            complete: function () {
                setInputEnabled(true);
            }
        });
    });

    // --- CORRECTED KEYDOWN HANDLER ---
    messageInputField.on('keydown', function (e) {
        // Only interfere with keys if the mentions dropdown is active and visible.
        if (isMentioning && mentionsDropdown.is(':visible')) {
            const items = mentionsList.find('li');
            if (items.length === 0) return; // No items to navigate.

            let handled = false; // Flag to check if we've handled the key event.

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault(); // CRITICAL: Stop the cursor from moving.
                    handled = true;
                {
                    const activeItem = mentionsList.find('li.mention-active');
                    let nextItem = activeItem.next('li');
                    if (nextItem.length === 0) { // If at the end, wrap to the start.
                        nextItem = items.first();
                    }
                    activeItem.removeClass('mention-active');
                    nextItem.addClass('mention-active');
                    nextItem[0].scrollIntoView({ block: 'nearest' }); // Keep item visible.
                }
                    break;

                case 'ArrowUp':
                    e.preventDefault(); // CRITICAL: Stop the cursor from moving.
                    handled = true;
                {
                    const activeItem = mentionsList.find('li.mention-active');
                    let prevItem = activeItem.prev('li');
                    if (prevItem.length === 0) { // If at the start, wrap to the end.
                        prevItem = items.last();
                    }
                    activeItem.removeClass('mention-active');
                    prevItem.addClass('mention-active');
                    prevItem[0].scrollIntoView({ block: 'nearest' }); // Keep item visible.
                }
                    break;

                case 'Enter':
                case 'Tab':
                    e.preventDefault(); // CRITICAL: Stop form submission or tabbing away.
                    handled = true;
                {
                    const currentActiveItem = mentionsList.find('li.mention-active');
                    if (currentActiveItem.length) {
                        const username = currentActiveItem.data('username');
                        selectMention(username);
                    }
                }
                    break;

                case 'Escape':
                    e.preventDefault();
                    handled = true;
                    isMentioning = false;
                    mentionsDropdown.hide();
                    break;
            }

            // If we handled the key, stop further execution of this function.
            if (handled) {
                return;
            }
        }

        // This part only runs if the key was NOT handled by the mention logic above.
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatInputForm.submit();
        }
    });

    chatHistoryArea.on('click', '.delete-message-btn', function () {
        const userMessageBubble = $(this).closest('.chat');
        const userMessageId = $(this).data('message-id');
        const assistantMessageBubble = userMessageBubble.nextAll('.chat-start').first();
        if (!confirm('Are you sure you want to delete this message and its response?')) return;
        $.ajax({
            url: `/api/group-chat/messages/${userMessageId}`,
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    userMessageBubble.fadeOut(300, function () { $(this).remove(); });
                    if (data.deleted_assistant_id && assistantMessageBubble.attr('id') === 'message-' + data.deleted_assistant_id) {
                        assistantMessageBubble.fadeOut(300, function () { $(this).remove(); });
                    }
                } else {
                    alert(data.error || 'Could not delete messages.');
                }
            },
            error: function () {
                alert('An error occurred while trying to delete messages.');
            }
        });
    });

    // Copy and TTS logic is identical to chat.js, so it's omitted for brevity but should be included here.
    // ...
    if (chatHistoryArea.children('.chat').length > 0) {
        scrollToBottom();
    }
    messageInputField.focus();
    autoResizeTextarea();

    if (groupChatHeaderIdInput.val()) {
        pollingInterval = setInterval(pollForUpdates, 3000); // Poll every 5 seconds
    }

    // Clean up interval when user navigates away to prevent memory leaks
    $(window).on('beforeunload', function() {
        clearInterval(pollingInterval);
    });
});
