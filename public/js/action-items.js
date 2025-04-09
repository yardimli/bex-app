$(document).ready(function() {
	const modal = $('#actionItemsModal');
	const list = $('#actionItemsList'); // Target for the dynamic list
	const input = $('#newActionItemInput');
	const addButton = $('#addActionItemButton');
	const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token
	
	// --- Function to render a single action item ---
	function renderActionItem(item) {
		const isChecked = item.is_done ? 'checked' : '';
		const itemClass = item.is_done ? 'item-done' : '';
		const dueDateHtml = item.due_date ? `<br><small class="text-muted ms-4">Due: ${item.due_date}</small>` : ''; // Basic format
		// Sanitize content before inserting
		const safeContent = $('<div>').text(item.content).html();
		
		return `
            <li class="list-group-item d-flex justify-content-between align-items-center ${itemClass}" data-id="${item.id}">
                <div>
                    <input class="form-check-input me-2 action-item-checkbox" type="checkbox" value="" id="action-${item.id}" ${isChecked}>
                    <label class="form-check-label" for="action-${item.id}">
                        ${safeContent}
                    </label>
                    ${dueDateHtml}
                </div>
                <button class="btn btn-sm btn-outline-danger ms-2 delete-action-item-btn" title="Delete item">
                    <i class="bi bi-trash"></i>
                </button>
            </li>
        `;
	}
	
	// --- Function to load action items ---
	function loadActionItems() {
		list.html('<li>Loading action items...</li>'); // Show loading state
		
		$.ajax({
			url: '/api/action-items', // Make sure this route is correct
			method: 'GET',
			dataType: 'json',
			success: function(items) {
				list.empty(); // Clear previous items/loading message
				if (items.length > 0) {
					items.forEach(item => {
						list.append(renderActionItem(item));
					});
				} else {
					list.html('<li class="list-group-item text-muted">No action items found.</li>');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error loading action items:", textStatus, errorThrown, jqXHR.responseText);
				list.html('<li class="list-group-item text-danger">Could not load action items. Please try again later.</li>');
			}
		});
	}
	
	// --- Load items when modal is shown ---
	modal.on('show.bs.modal', function() {
		// Check if the list element exists (i.e., user is logged in)
		if (list.length) {
			loadActionItems();
		}
	});
	
	// --- Add new item ---
	addButton.on('click', function() {
		const content = input.val().trim();
		if (!content) {
			alert('Please enter an action item.');
			return;
		}
		
		addButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
		
		
		$.ajax({
			url: '/api/action-items',
			method: 'POST',
			data: JSON.stringify({ // Send as JSON
				content: content,
				_token: csrfToken // Include CSRF token if using web routes directly without API setup
			}),
			contentType: 'application/json', // Set content type
			headers: { // Alternatively send token via header
				'X-CSRF-TOKEN': csrfToken
			},
			dataType: 'json',
			success: function(newItem) {
				// Remove "No items" message if it exists
				if (list.find('.text-muted').length > 0 && list.children().length === 1) {
					list.empty();
				}
				list.append(renderActionItem(newItem));
				input.val(''); // Clear input
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error adding action item:", textStatus, errorThrown, jqXHR.responseText);
				alert('Could not add action item. Error: ' + (jqXHR.responseJSON?.message || 'Please try again.'));
			},
			complete: function() {
				addButton.prop('disabled', false).html('<i class="bi bi-plus-lg me-1"></i> Add'); // Restore button
			}
		});
	});
	
	// Allow adding with Enter key
	input.on('keypress', function(e) {
		if (e.which === 13) { // Enter key pressed
			e.preventDefault(); // Prevent form submission (if it's in a form)
			addButton.click(); // Trigger the add button click
		}
	});
	
	
	// --- Toggle item done status (using event delegation) ---
	list.on('change', '.action-item-checkbox', function() {
		const checkbox = $(this);
		const listItem = checkbox.closest('.list-group-item');
		const itemId = listItem.data('id');
		const isDone = checkbox.is(':checked');
		
		// Optimistic UI update
		listItem.toggleClass('item-done', isDone);
		
		$.ajax({
			url: `/api/action-items/${itemId}`,
			method: 'PATCH', // Use PATCH
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
				// Update potentially changed data if needed, though toggleClass is usually enough
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("Error updating action item:", textStatus, errorThrown, jqXHR.responseText);
				alert('Could not update item status.');
				// Revert optimistic update on error
				checkbox.prop('checked', !isDone);
				listItem.toggleClass('item-done', !isDone);
			}
		});
	});
	
	// --- Delete item (using event delegation) ---
	list.on('click', '.delete-action-item-btn', function() {
		const button = $(this);
		const listItem = button.closest('.list-group-item');
		const itemId = listItem.data('id');
		const itemContent = listItem.find('.form-check-label').text().trim();
		
		if (confirm(`Are you sure you want to delete this item?\n\n"${itemContent}"`)) {
			// Add temporary visual feedback
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
							// Check if list is now empty
							if (list.children().length === 0) {
								list.html('<li class="list-group-item text-muted">No action items found.</li>');
							}
						});
					} else {
						alert(response.message || 'Could not delete item.');
						listItem.css('opacity', '1'); // Restore opacity
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error("Error deleting action item:", textStatus, errorThrown, jqXHR.responseText);
					alert('Could not delete item. Please try again.');
					listItem.css('opacity', '1'); // Restore opacity
				}
			});
		}
	});
	
});
