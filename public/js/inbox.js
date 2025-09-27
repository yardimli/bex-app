// public/js/inbox.js:

$(document).ready(function() {
    const inboxList = $('#inbox-list');
    const sentList = $('#sent-list');
    const unreadFilter = $('#unread-filter');
    const teamFilter = $('#team-filter');
    const messageDetailModal = document.getElementById('messageDetailModal');
    const unreadFilterContainer = $('#unread-filter-container');
    
    // --- Tab Management ---
    $('input[name="inbox_tabs"]').on('change', function() {
        if ($('#inbox-tab-radio').is(':checked')) {
            unreadFilterContainer.show();
            loadInbox();
        } else {
            unreadFilterContainer.hide();
            loadSent();
        }
    });
    
    // --- Inbox Loading ---
    function loadInbox(url = '/api/user/inbox') {
        const params = {
            unread: unreadFilter.is(':checked') ? 1 : 0,
            team_id: teamFilter.val()
        };
        showLoading(inboxList, 'Loading messages...');
        
        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                inboxList.empty();
                if (response.data.length > 0) {
                    response.data.forEach(item => {
                        inboxList.append(renderInboxMessageItem(item));
                    });
                    renderPagination(response, $('#inbox-pagination-links'), loadInbox);
                } else {
                    inboxList.html('<div class="p-4 text-base-content/70">Your inbox is empty.</div>');
                    $('#inbox-pagination-links').empty();
                }
            },
            error: function() {
                inboxList.html('<div class="p-4 alert alert-error">Could not load messages.</div>');
            }
        });
    }
    
    // --- Sent Messages Loading ---
    function loadSent(url = '/api/user/sent') {
        const params = {
            team_id: teamFilter.val()
        };
        showLoading(sentList, 'Loading sent messages...');
        
        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                sentList.empty();
                if (response.data.length > 0) {
                    response.data.forEach(message => {
                        sentList.append(renderSentMessageItem(message));
                    });
                    renderPagination(response, $('#sent-pagination-links'), loadSent);
                } else {
                    sentList.html('<div class="p-4 text-base-content/70">You have not sent any messages.</div>');
                    $('#sent-pagination-links').empty();
                }
            },
            error: function() {
                sentList.html('<div class="p-4 alert alert-error">Could not load sent messages.</div>');
            }
        });
    }
    
    // --- Rendering ---
    function showLoading(element, text) {
        element.html(`<div class="text-center p-5"><span class="loading loading-spinner loading-lg text-primary"></span><p class="mt-2">${text}</p></div>`);
    }
    
    function renderInboxMessageItem(item) {
        const isUnread = !item.read_at;
        const message = item.message;
        const sentDate = new Date(message.created_at).toLocaleString();
        const itemHtml = `
            <a href="#" class="block p-4 rounded-lg border-l-4 transition-colors duration-200 ease-in-out hover:bg-base-200 ${isUnread ? 'border-primary font-semibold' : 'border-transparent'}"
               data-message-id="${message.id}"
               data-message-type="inbox">
                <div class="flex w-full justify-between">
                    <p class="mb-1 truncate">${$('<div>').text(message.subject).html()}</p>
                    <small class="text-base-content/70 flex-shrink-0 ml-4">${new Date(message.created_at).toLocaleDateString()}</small>
                </div>
                <p class="mb-1 text-base-content/70 text-sm">From: ${$('<div>').text(message.sender.name).html()} | Team: ${$('<div>').text(message.team.name).html()}</p>
            </a>`;
        return itemHtml;
    }
    
    function renderSentMessageItem(message) {
        const messageData = escape(JSON.stringify(message));
        const recipientsSummary = message.recipients.length > 2
          ? `${$('<div>').text(message.recipients[0].recipient.name).html()}, ${$('<div>').text(message.recipients[1].recipient.name).html()} and ${message.recipients.length - 2} more`
          : message.recipients.map(r => $('<div>').text(r.recipient.name).html()).join(', ');
        
        const itemHtml = `
            <a href="#" class="block p-4 rounded-lg border-l-4 border-transparent transition-colors duration-200 ease-in-out hover:bg-base-200"
               data-message-type="sent"
               data-message-data='${messageData}'>
                <div class="flex w-full justify-between">
                    <p class="mb-1 truncate">${$('<div>').text(message.subject).html()}</p>
                    <small class="text-base-content/70 flex-shrink-0 ml-4">${new Date(message.created_at).toLocaleDateString()}</small>
                </div>
                <p class="mb-1 text-base-content/70 text-sm">To: ${recipientsSummary} | Team: ${$('<div>').text(message.team.name).html()}</p>
                <div class="read-status text-xs text-base-content/70">
                    <i class="bi bi-check2-all"></i> Read by ${message.read_count} of ${message.total_recipients}
                </div>
            </a>`;
        return itemHtml;
    }
    
    function renderPagination(response, container, callback) {
        container.empty();
        const links = response.links;
        if (!links || links.length <= 3) return;
        
        const nav = $('<div class="join"></div>');
        links.forEach(link => {
            let btnClass = 'join-item btn btn-sm';
            if (link.active) btnClass += ' btn-active';
            if (!link.url) btnClass += ' btn-disabled';
            
            const linkEl = $(`<button class="${btnClass}" data-url="${link.url}">${link.label}</button>`);
            linkEl.on('click', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                if (url) {
                    callback(url);
                }
            });
            nav.append(linkEl);
        });
        container.append(nav);
    }
    
    // --- Modal Handling ---
    $(document).on('click', 'a[data-message-type]', function(e) {
        e.preventDefault();
        const item = $(this);
        const messageType = item.data('message-type');
        const metaInfoContainer = $('#message-meta-info');
        const recipientListContainer = $('#recipient-status-list');
        
        recipientListContainer.hide().empty(); // Reset recipient list
        
        if (messageType === 'inbox') {
            const messageId = item.data('message-id');
            // Fetch full details to avoid storing large data in attributes
            $.get(`/api/messages/${messageId}`, function(message) {
                $('#message-subject').text(message.subject);
                $('#message-body').html(message.body.replace(/\n/g, '<br>'));
                metaInfoContainer.html(`
                    <div class="text-sm text-base-content/70">
                        <strong>From:</strong> <span>${$('<div>').text(message.sender.name).html()}</span><br>
                        <strong>Team:</strong> <span>${$('<div>').text(message.team.name).html()}</span><br>
                        <strong>Date:</strong> <span>${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                `);
                messageDetailModal.showModal();
            });
            
            if (item.hasClass('border-primary')) { // Check for unread class
                $.ajax({
                    url: `/api/messages/${messageId}/read`,
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        item.removeClass('border-primary font-semibold').addClass('border-transparent');
                        updateUnreadCountInNav();
                    }
                });
            }
        } else if (messageType === 'sent') {
            const message = JSON.parse(unescape(item.data('message-data')));
            const sentDate = new Date(message.created_at).toLocaleString();
            
            $('#message-subject').text(message.subject);
            $('#message-body').html(message.body.replace(/\n/g, '<br>'));
            
            metaInfoContainer.html(`
                <div class="text-sm text-base-content/70">
                    <strong>Team:</strong> <span>${$('<div>').text(message.team.name).html()}</span><br>
                    <strong>Date:</strong> <span>${sentDate}</span>
                </div>
            `);
            
            let recipientHtml = '<h4 class="font-semibold mt-4">Recipient Status</h4><ul class="list-disc list-inside">';
            message.recipients.forEach(r => {
                const readStatus = r.read_at
                  ? `<span class="text-success text-sm">(Read on ${new Date(r.read_at).toLocaleDateString()})</span>`
                  : `<span class="text-base-content/70 text-sm">(Unread)</span>`;
                recipientHtml += `<li>${$('<div>').text(r.recipient.name).html()} ${readStatus}</li>`;
            });
            recipientHtml += '</ul>';
            recipientListContainer.html(recipientHtml).show();
            
            messageDetailModal.showModal();
        }
    });
    
    // --- Filters ---
    unreadFilter.on('change', () => loadInbox());
    teamFilter.on('change', function() {
        if ($('#inbox-tab-radio').is(':checked')) {
            loadInbox();
        } else {
            loadSent();
        }
    });
    
    // --- Initial Load ---
    loadInbox();
});

// This function can be called from anywhere to update the nav count
function updateUnreadCountInNav() {
    const countElement = $('#unread-messages-count');
    if (countElement.length) {
        $.get('/api/user/unread-count', function(data) {
            if (data.unread_count > 0) {
                countElement.text(data.unread_count).show();
            } else {
                countElement.hide();
            }
        });
    }
}
