$(document).ready(function() {
    const inboxList = $('#inbox-list');
    const sentList = $('#sent-list');
    const unreadFilter = $('#unread-filter');
    const teamFilter = $('#team-filter');
    const messageDetailModal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
    const unreadFilterContainer = $('#unread-filter-container');

    // --- Tab Management ---
    $('#inbox-tab').on('shown.bs.tab', function() {
        unreadFilterContainer.show();
        loadInbox();
    });

    $('#sent-tab').on('shown.bs.tab', function() {
        unreadFilterContainer.hide();
        loadSent();
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
                    inboxList.html('<div class="list-group-item text-muted">Your inbox is empty.</div>');
                    $('#inbox-pagination-links').empty();
                }
            },
            error: function() {
                inboxList.html('<div class="list-group-item list-group-item-danger">Could not load messages.</div>');
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
                    sentList.html('<div class="list-group-item text-muted">You have not sent any messages.</div>');
                    $('#sent-pagination-links').empty();
                }
            },
            error: function() {
                sentList.html('<div class="list-group-item list-group-item-danger">Could not load sent messages.</div>');
            }
        });
    }

    // --- Rendering ---
    function showLoading(element, text) {
        element.html(`<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">${text}</p></div>`);
    }

    function renderInboxMessageItem(item) {
        const isUnread = !item.read_at;
        const message = item.message;
        const sentDate = new Date(message.created_at).toLocaleString();
        // The data attributes for subject, body, sender, team, and date were added back here.
        const itemHtml = `
            <a href="#" class="list-group-item list-group-item-action inbox-item ${isUnread ? 'unread' : ''}"
               data-message-id="${message.id}"
               data-message-type="inbox"
               data-subject="${escape(message.subject)}"
               data-body="${escape(message.body)}"
               data-sender="${escape(message.sender.name)}"
               data-team="${escape(message.team.name)}"
               data-date="${escape(sentDate)}">
                <div class="d-flex w-100 justify-content-between">
                    <p class="mb-1">${$('<div>').text(message.subject).html()}</p>
                    <small class="text-muted">${new Date(message.created_at).toLocaleDateString()}</small>
                </div>
                <p class="mb-1 text-muted small">From: ${$('<div>').text(message.sender.name).html()} | Team: ${$('<div>').text(message.team.name).html()}</p>
            </a>`;
        return itemHtml;
    }

    function renderSentMessageItem(message) {
        // Store the full message object data to use when opening the modal
        const messageData = escape(JSON.stringify(message));
        const recipientsSummary = message.recipients.length > 2
            ? `${$('<div>').text(message.recipients[0].recipient.name).html()}, ${$('<div>').text(message.recipients[1].recipient.name).html()} and ${message.recipients.length - 2} more`
            : message.recipients.map(r => $('<div>').text(r.recipient.name).html()).join(', ');

        const itemHtml = `
            <a href="#" class="list-group-item list-group-item-action sent-item"
               data-message-type="sent"
               data-message-data='${messageData}'>
                <div class="d-flex w-100 justify-content-between">
                    <p class="mb-1">${$('<div>').text(message.subject).html()}</p>
                    <small class="text-muted">${new Date(message.created_at).toLocaleDateString()}</small>
                </div>
                <p class="mb-1 text-muted small">To: ${recipientsSummary} | Team: ${$('<div>').text(message.team.name).html()}</p>
                <div class="read-status">
                    <i class="bi bi-check2-all"></i> Read by ${message.read_count} of ${message.total_recipients}
                </div>
            </a>`;
        return itemHtml;
    }

    function renderPagination(response, container, callback) {
        container.empty();
        const links = response.links;
        if (!links || links.length <= 3) return;

        const nav = $('<nav><ul class="pagination"></ul></nav>');
        links.forEach(link => {
            const liClass = `page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`;
            const linkEl = $(`<li class="${liClass}"><a class="page-link" href="#" data-url="${link.url}">${link.label}</a></li>`);
            linkEl.on('click', 'a', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                if (url) {
                    callback(url);
                }
            });
            nav.find('ul').append(linkEl);
        });
        container.append(nav);
    }

    // --- Modal Handling ---
    $(document).on('click', '.inbox-item, .sent-item', function(e) {
        e.preventDefault();
        const item = $(this);
        const messageType = item.data('message-type');
        const metaInfoContainer = $('#message-meta-info');
        console.log('metaInfoContainer:');
        console.log(metaInfoContainer);
        const recipientListContainer = $('#recipient-status-list');

        recipientListContainer.hide().empty(); // Reset recipient list

        if (messageType === 'inbox') {
            const messageId = item.data('message-id');
            $('#message-subject').text(unescape(item.data('subject')));
            $('#message-body').html(unescape(item.data('body')).replace(/\n/g, '<br>'));

            metaInfoContainer.html(`
                <p class="text-muted small">
                    <strong>From:</strong> <span>${unescape(item.data('sender'))}</span><br>
                    <strong>Team:</strong> <span>${unescape(item.data('team'))}</span><br>
                    <strong>Date:</strong> <span>${unescape(item.data('date'))}</span>
                </p>
            `);

            messageDetailModal.show();

            if (item.hasClass('unread')) {
                $.ajax({
                    url: `/api/messages/${messageId}/read`,
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        item.removeClass('unread');
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
                <p class="text-muted small">
                    <strong>Team:</strong> <span>${$('<div>').text(message.team.name).html()}</span><br>
                    <strong>Date:</strong> <span>${sentDate}</span>
                </p>
            `);

            // Build and show recipient status list
            // let recipientHtml = '<h6>Recipient Status</h6><ul class="list-unstyled">';
            // message.recipients.forEach(r => {
            //     const readStatus = r.read_at
            //         ? `<span class="text-success small">(Read on ${new Date(r.read_at).toLocaleDateString()})</span>`
            //         : `<span class="text-muted small">(Unread)</span>`;
            //     recipientHtml += `<li>${$('<div>').text(r.recipient.name).html()} ${readStatus}</li>`;
            // });
            // recipientHtml += '</ul>';
            // recipientListContainer.html(recipientHtml).show();

            messageDetailModal.show();
        }
    });

    // --- Filters ---
    unreadFilter.on('change', () => loadInbox());
    teamFilter.on('change', function() {
        if ($('#inbox-tab').hasClass('active')) {
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
