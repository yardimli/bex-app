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

    let currentAudio = null;
    let currentReadAloudButton = null;

    function scrollToBottom() {
        chatHistoryArea.scrollTop(chatHistoryArea[0].scrollHeight);
    }

    function addMessageBubble(role, content, messageId, user = null, canDelete = false) {
        const isAssistant = role === 'assistant';
        const isCurrentUser = !isAssistant && user && user.id === currentUserId;

        const alignment = isAssistant ? 'chat-start' : 'chat-end';
        const bubbleColor = isCurrentUser ? 'chat-bubble-primary' : '';
        const escapedContentHtml = $('<div>').text(content).html().replace(/\n/g, '<br>');
        const now = new Date();
        const timeString = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

        const deleteButtonHtml = (isCurrentUser && canDelete)
            ? `<button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn" title="Delete pair" data-message-id="${messageId}">
                   <i class="bi bi-trash3-fill"></i>
               </button>`
            : '';
        const senderName = isAssistant ? 'Bex' : (user ? user.name : 'Unknown User');
        const escapedSenderName = $('<div>').text(senderName).html();

        let assistantButtons = '';
        if (isAssistant) {
            assistantButtons = `
                <button class="btn btn-ghost btn-xs copy-btn" title="Copy text"><i class="bi bi-clipboard"></i></button>
                <button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud">
                    <i class="bi bi-play-circle"></i>
                    <span class="loading loading-spinner loading-xs" style="display: none;"></span>
                </button>
            `;
        }

        const footerHtml = `
            <div class="chat-footer opacity-50 flex items-center gap-2 mt-1">
                <span class="text-xs font-semibold">${escapedSenderName}</span>
                <time class="text-xs">${timeString}</time>
                <div class="flex-grow"></div>
                ${assistantButtons}
            </div>`;

        const bubbleHtml = `
            <div class="chat ${alignment}" id="message-${messageId}" data-message-content="${escape(content)}">
                <div class="chat-bubble ${bubbleColor} relative">
                    ${escapedContentHtml}
                    ${deleteButtonHtml}
                </div>
                ${footerHtml}
            </div>`;

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

    messageInputField.on('input', autoResizeTextarea);
    autoResizeTextarea();

    chatInputForm.on('submit', function (e) {
        e.preventDefault();
        $('#empty-conversation').remove();

        const message = messageInputField.val().trim();
        if (!message) return;

        const groupChatHeaderId = groupChatHeaderIdInput.val();
        const teamId = teamIdInput.val();
        const selectedModel = localStorage.getItem('selectedLlmModel') || 'openai/gpt-4o-mini';

        setInputEnabled(false);

        const tempUserMessageId = 'temp-user-' + Date.now();
        // Optimistically add user message
        addMessageBubble('user', message, tempUserMessageId, { id: currentUserId, name: 'You' }, false);
        scrollToBottom();
        messageInputField.val('');
        autoResizeTextarea();

        $.ajax({
            url: '/api/group-chat/store',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: message,
                team_id: teamId,
                group_chat_header_id: groupChatHeaderId || null,
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#message-' + tempUserMessageId).remove();
                    addMessageBubble(data.user_message.role, data.user_message.content, data.user_message.id, data.user_message.user, true);
                    if (data.assistant_message) {
                        addMessageBubble(data.assistant_message.role, data.assistant_message.content, data.assistant_message.id, null, false);
                    }

                    if (data.is_new_chat && data.group_chat_header_id) {
                        groupChatHeaderIdInput.val(data.group_chat_header_id);
                        const newUrl = `/team/${teamId}/group-chat/${data.group_chat_header_id}`;
                        history.pushState({ chatId: data.group_chat_header_id }, '', newUrl);

                        const newTitle = data.updated_title || 'Group Chat ' + data.group_chat_header_id;
                        const newLinkHtml = `
                         <li>
                            <a href="${newUrl}" id="chat-link-${data.group_chat_header_id}" title="${newTitle}" class="active justify-between">
                                <span class="truncate">${newTitle.substring(0, 25)}</span>
                                <button class="btn btn-ghost btn-xs btn-circle delete-chat-btn" data-chat-id="${data.group_chat_header_id}" data-type="group">
                                    <i class="bi bi-trash text-error"></i>
                                </button>
                            </a>
                         </li>`;
                        sidebarMenu.find('a').removeClass('active');
                        sidebarMenu.prepend(newLinkHtml);
                        sidebarMenu.find('.text-base-content\\/60').parent().remove();
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

    messageInputField.on('keydown', function (e) {
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
});
