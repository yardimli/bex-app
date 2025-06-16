$(document).ready(function() {
    const inboxList = $('#inbox-list');
    const unreadFilter = $('#unread-filter');
    const teamFilter = $('#team-filter');
    const messageDetailModal = new bootstrap.Modal(document.getElementById('messageDetailModal'));

    function loadInbox(url = '/api/user/inbox') {
        const params = {
            unread: unreadFilter.is(':checked') ? 1 : 0,
            team_id: teamFilter.val()
        };

        inboxList.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading messages...</p></div>');

        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                inboxList.empty();
                if (response.data.length > 0) {
                    response.data.forEach(item => {
                        inboxList.append(renderMessageItem(item));
                    });
                    renderPagination(response.links);
                } else {
                    inboxList.html('<div class="list-group-item text-muted">Your inbox is empty.</div>');
                    $('#pagination-links').empty();
                }
            },
            error: function() {
                inboxList.html('<div class="list-group-item list-group-item-danger">Could not load messages.</div>');
            }
        });
    }

    function renderMessageItem(item) {
        const isUnread = !item.read_at;
        const message = item.message;
        const sentDate = new Date(message.created_at).toLocaleString();

        const itemHtml = `
            <a href="#" class="list-group-item list-group-item-action inbox-item ${isUnread ? 'unread' : ''}"
               data-message-id="${message.id}"
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
            </a>
        `;
        return itemHtml;
    }

    function renderPagination(links) {
        const paginationContainer = $('#pagination-links');
        paginationContainer.empty();
        if (!links || links.length <= 3) return; // No need for pagination if only prev, current, next exist

        const nav = $('<nav><ul class="pagination"></ul></nav>');
        links.forEach(link => {
            const liClass = `page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`;
            const linkEl = $(`<li class="${liClass}"><a class="page-link" href="#" data-url="${link.url}">${link.label}</a></li>`);
            nav.find('ul').append(linkEl);
        });
        paginationContainer.append(nav);
    }

    inboxList.on('click', '.inbox-item', function(e) {
        e.preventDefault();
        const item = $(this);
        const messageId = item.data('message-id');

        // Populate and show modal
        $('#message-subject').text(unescape(item.data('subject')));
        $('#message-from').text(unescape(item.data('sender')));
        $('#message-team').text(unescape(item.data('team')));
        $('#message-date').text(unescape(item.data('date')));
        $('#message-body').text(unescape(item.data('body')));
        messageDetailModal.show();

        // Mark as read if it was unread
        if (item.hasClass('unread')) {
            $.ajax({
                url: `/api/messages/${messageId}/read`,
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    item.removeClass('unread');
                    updateUnreadCountInNav(); // Update global count
                }
            });
        }
    });

    $('#pagination-links').on('click', 'a.page-link', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        if (url) {
            loadInbox(url);
        }
    });

    // Filters
    unreadFilter.on('change', () => loadInbox());
    teamFilter.on('change', () => loadInbox());

    // Initial load
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
