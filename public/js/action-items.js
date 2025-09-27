// public/js/action-items.js:

$(document).ready(function() {
	const modal = document.getElementById('actionItemsModal');
	const list = $('#actionItemsList'); // Target for the dynamic list
	const input = $('#newActionItemInput');
	const addButton = $('#addActionItemButton');
	const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token
	
	// --- Function to render a single action item ---
	function renderActionItem(item) {
		const isChecked = item.is_done ? 'checked' : '';
		const itemClass = item.is_done ? 'item-done opacity-60' : '';
		const dueDateHtml = item.due_date ? `<br><small class="text-base-content/70 ms-4">Due: ${item.due_date}</small>` : '';
		const safeContent = $('<div>').text(item.content).html();
		
		return `
            <li class="flex justify-between items-center p-3 bg-base-200 rounded-lg ${itemClass}" data-id="${item.id}">
                <div class="flex-grow">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" class="checkbox checkbox-primary action-item-checkbox" id="action-${item.id}" ${isChecked} />
                        <span class="label-text ${item.is_done ? 'line-through' : ''}">
                            ${safeContent}
                        </span>
                    </label>
                    ${dueDateHtml}
                </div>
                <button class="btn btn-ghost btn-sm btn-circle text-error delete-action-item-btn" title="Delete item">
                    <i class="bi bi-trash"></i>
                </button>
            </li>
        `;
	}
	
	// --- Function to load action items ---
	function loadActionItems() {
		list.html('<li>Loading action items...</li>'); // Show loading state
		
		$.ajax({
			url: '/api/action-items',
			method: 'GET',
			dataType: 'json',
			success: function(items) {
				list.empty();
				if (items.length > 0) {
					items.forEach(item => {
						list.append(renderActionItem(item));
					});
				} else {
					list.html('<li class="p-4 text-center text-base-content/70">No action items found.</li>');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error loading action items:", textStatus, errorThrown, jqXHR.responseText);
				list.html('<li class="p-4 text-center text-error">Could not load action items. Please try again later.</li>');
			}
		});
	}
	
	// --- Load items when the modal is triggered to be shown ---
	$('#actionItemsButton').on('click', function() {
		if (list.length) {
			loadActionItems();
		}
		modal.showModal();
	});
	
	// --- Add new item ---
	addButton.on('click', function() {
		const content = input.val().trim();
		if (!content) {
			alert('Please enter an action item.');
			return;
		}
		
		addButton.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Adding...');
		
		$.ajax({
			url: '/api/action-items',
			method: 'POST',
			data: JSON.stringify({
				content: content,
				_token: csrfToken
			}),
			contentType: 'application/json',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			dataType: 'json',
			success: function(newItem) {
				if (list.find('.text-base-content\\/70').length > 0 && list.children().length === 1) {
					list.empty();
				}
				list.append(renderActionItem(newItem));
				input.val('');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error adding action item:", textStatus, errorThrown, jqXHR.responseText);
				alert('Could not add action item. Error: ' + (jqXHR.responseJSON?.message || 'Please try again.'));
			},
			complete: function() {
				addButton.prop('disabled', false).html('<i class="bi bi-plus-lg me-1"></i> Add');
			}
		});
	});
	
	input.on('keypress', function(e) {
		if (e.which === 13) {
			e.preventDefault();
			addButton.click();
		}
	});
	
	// --- Toggle item done status (using event delegation) ---
	list.on('change', '.action-item-checkbox', function() {
		const checkbox = $(this);
		const listItem = checkbox.closest('li');
		const itemId = listItem.data('id');
		const isDone = checkbox.is(':checked');
		
		// Optimistic UI update
		listItem.toggleClass('opacity-60', isDone);
		listItem.find('.label-text').toggleClass('line-through', isDone);
		
		$.ajax({
			url: `/api/action-items/${itemId}`,
			method: 'PATCH',
			data: JSON.stringify({
				is_done: isDone,
				_token: csrfToken
			}),
			contentType: 'application/json',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			dataType: 'json',
			success: function(updatedItem) {
				console.log('Item status updated:', updatedItem);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error updating action item:", textStatus, errorThrown, jqXHR.responseText);
				alert('Could not update item status.');
				// Revert optimistic update on error
				checkbox.prop('checked', !isDone);
				listItem.toggleClass('opacity-60', !isDone);
				listItem.find('.label-text').toggleClass('line-through', !isDone);
			}
		});
	});
	
	// --- Delete item (using event delegation) ---
	list.on('click', '.delete-action-item-btn', function() {
		const button = $(this);
		const listItem = button.closest('li');
		const itemId = listItem.data('id');
		const itemContent = listItem.find('.label-text').text().trim();
		
		if (confirm(`Are you sure you want to delete this item?\n\n"${itemContent}"`)) {
			listItem.css('opacity', '0.5');
			
			$.ajax({
				url: `/api/action-items/${itemId}`,
				method: 'DELETE',
				headers: {
					'X-CSRF-TOKEN': csrfToken
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						listItem.fadeOut(300, function() {
							$(this).remove();
							if (list.children().length === 0) {
								list.html('<li class="p-4 text-center text-base-content/70">No action items found.</li>');
							}
						});
					} else {
						alert(response.message || 'Could not delete item.');
						listItem.css('opacity', '1');
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error("Error deleting action item:", textStatus, errorThrown, jqXHR.responseText);
					alert('Could not delete item. Please try again.');
					listItem.css('opacity', '1');
				}
			});
		}
	});
});
