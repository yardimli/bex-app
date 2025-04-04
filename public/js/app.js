$(document).ready(function () {
	const themeToggleButton = $('#themeToggleButton');
	const htmlElement = $('html');
	const currentTheme = localStorage.getItem('theme');
	const lightIcon = 'bi-brightness-high-fill';
	const darkIcon = 'bi-moon-stars-fill';
	
	
	// --- Sidebar Toggle Elements ---
	const sidebarToggle = $('#sidebarToggle');
	const body = $('body');
	const sidebarBackdrop = $('.sidebar-backdrop');
	const breakpoint = 991.98; // Bootstrap LG breakpoint
	
	// Function to check if we are on a mobile-sized screen
	function isMobile() {
		return $(window).width() <= breakpoint;
	}
	
	// Function to apply the correct sidebar state based on screen size and local storage
	function applySidebarState() {
		if (isMobile()) {
			// On mobile, always start collapsed, remove desktop class
			body.removeClass('sidebar-collapsed');
			body.removeClass('sidebar-mobile-shown'); // Ensure it starts hidden
			sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list'); // Reset icon
		} else {
			// On desktop, check local storage
			const desktopState = localStorage.getItem('sidebarState');
			if (desktopState === 'collapsed') {
				body.addClass('sidebar-collapsed');
				sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list');
			} else {
				body.removeClass('sidebar-collapsed'); // Default is expanded
				sidebarToggle.find('i').removeClass('bi-list').addClass('bi-x');
			}
			// Ensure mobile class/backdrop are hidden on desktop
			body.removeClass('sidebar-mobile-shown');
		}
	}
	
	// --- Sidebar Toggle Logic ---
	sidebarToggle.on('click', function () {
		const icon = $(this).find('i');
		if (isMobile()) {
			body.toggleClass('sidebar-mobile-shown');
			// Toggle icon on mobile
			if (body.hasClass('sidebar-mobile-shown')) {
				icon.removeClass('bi-list').addClass('bi-x');
			} else {
				icon.removeClass('bi-x').addClass('bi-list');
			}
		} else {
			body.toggleClass('sidebar-collapsed');
			// Save desktop state and toggle icon
			if (body.hasClass('sidebar-collapsed')) {
				localStorage.setItem('sidebarState', 'collapsed');
				icon.removeClass('bi-x').addClass('bi-list');
			} else {
				localStorage.setItem('sidebarState', 'expanded');
				icon.removeClass('bi-list').addClass('bi-x');
			}
		}
	});
	
	// Click backdrop to hide sidebar on mobile
	sidebarBackdrop.on('click', function () {
		if (isMobile() && body.hasClass('sidebar-mobile-shown')) {
			body.removeClass('sidebar-mobile-shown');
			sidebarToggle.find('i').removeClass('bi-x').addClass('bi-list'); // Reset icon
		}
	});
	
	// Re-apply state on window resize
	let resizeTimer;
	$(window).on('resize', function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function () {
			// Apply state after resize debounce
			applySidebarState();
		}, 250); // Debounce resize event
	});
	
	// Initial Sidebar State Application
	applySidebarState();
	// --- End Sidebar Toggle Logic ---
	
	
	// Function to apply theme and save preference
	function applyTheme(theme) {
		if (theme === 'dark') {
			htmlElement.addClass('dark-mode');
			themeToggleButton.find('i').removeClass(lightIcon).addClass(darkIcon);
			localStorage.setItem('theme', 'dark');
		} else {
			htmlElement.removeClass('dark-mode');
			themeToggleButton.find('i').removeClass(darkIcon).addClass(lightIcon);
			localStorage.setItem('theme', 'light');
		}
	}
	
	// Apply saved theme on load or default to light
	applyTheme(currentTheme || 'light');
	
	// --- Modal Triggers ---
	$('#meetingSummaryButton').on('click', function () {
		$('#recentMeetingsModal').modal('show');
	});
	
	$('#actionItemsButton').on('click', function () {
		$('#actionItemsModal').modal('show');
	});
	
	$('#myNotesButton').on('click', function () {
		$('#myNotesModal').modal('show');
	});
	
	$('#myRecordingsButton').on('click', function () {
		$('#myRecordingsModal').modal('show');
	});
	
	$('#summarizeButton').on('click', function () {
		$('#summarizeContentModal').modal('show');
	});
	
	$('#transcribeButton').on('click', function () {
		$('#transcribeModal').modal('show');
	});
	
	$('#teamWorkspaceButton').on('click', function () {
		$('#teamFilesModal').modal('show');
	});
	
	$('#settingsButton').on('click', function () {
		$('#settingsModal').modal('show');
	});
	
	themeToggleButton.on('click', function (e) {
		e.preventDefault(); // Prevent default link behavior
		const newTheme = htmlElement.hasClass('dark-mode') ? 'light' : 'dark';
		applyTheme(newTheme);
		console.log('Theme changed to:', newTheme);
	});
	
	// --- Modal Dynamic Content (Simulation) ---
	
	// Generic handler for list items in two-pane modals
	function handleListItemClick(modalId, detailsSelector, placeholderText) {
		$(modalId + ' .list-group-item').on('click', function (e) {
			e.preventDefault(); // Prevent default link behavior
			
			// Highlight active item
			$(modalId + ' .list-group-item').removeClass('active');
			$(this).addClass('active');
			
			// Get item data (using data-id or text)
			const itemId = $(this).data('id');
			const itemTitle = $(this).find('strong').text() || $(this).text(); // Handle files modal structure
			
			// Update details pane (Simulated - replace with AJAX later)
			const detailsPane = $(modalId + ' ' + detailsSelector);
			detailsPane.html(`
                <div class="text-center">
                    <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading details for: <br><strong>${itemTitle}</strong></p>
                    <small class="text-muted">(ID: ${itemId || 'N/A'})</small>
                </div>
            `);
			
			// Simulate loading delay
			setTimeout(() => {
				detailsPane.html(`
                    <h4>Details for ${itemTitle}</h4>
                    <p>This is where the actual content, player, or preview for "${itemTitle}" would be loaded via AJAX.</p>
                    <p>Item ID: ${itemId || 'N/A'}</p>
                    ${modalId === '#myRecordingsModal' ? '<button class="btn btn-sm btn-primary mt-2"><i class="bi bi-play-fill"></i> Play Recording</button>' : ''}
                    ${modalId === '#teamFilesModal' ? '<button class="btn btn-sm btn-info mt-2"><i class="bi bi-eye-fill"></i> Preview File</button>' : ''}
                 `);
			}, 500); // 0.5 second delay
			
			console.log(`Item clicked in ${modalId}: ID=${itemId}, Title=${itemTitle}`);
			// --- TODO: Add AJAX call here ---
			// Example:
			// $.ajax({
			//     url: '/api/meetings/' + itemId, // Adjust URL based on modal type
			//     method: 'GET',
			//     success: function(data) {
			//         detailsPane.html(/* Render data */);
			//     },
			//     error: function() {
			//         detailsPane.html('<p class="text-danger">Error loading details.</p>');
			//     }
			// });
		});
	}
	
	handleListItemClick('#recentMeetingsModal', '.details-pane', 'Select a meeting to view details');
	handleListItemClick('#myNotesModal', '.details-pane', 'Select a note to view details');
	handleListItemClick('#myRecordingsModal', '.details-pane', 'Select a recording to play');
	handleListItemClick('#teamFilesModal', '.details-pane', 'Select a file to view details or preview'); // Note: target the specific details pane in team files
	
	// --- Specific Modal Interactions ---
	
	// Action Items - Add Item (Simulation)
	$('#addActionItemButton').on('click', function () {
		const newItemText = $('#newActionItemInput').val().trim();
		if (newItemText) {
			const newItemId = 'action' + Date.now(); // Simple unique ID
			const newItemHtml = `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <input class="form-check-input me-2" type="checkbox" value="" id="${newItemId}">
                        <label class="form-check-label" for="${newItemId}">
                            ${$('<div>').text(newItemText).html()} <!-- Basic XSS protection -->
                        </label>
                        <br><small class="text-muted ms-4">Due: Not set</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger ms-2 remove-item-btn"><i class="bi bi-trash"></i></button>
                </li>`;
			$('#actionItemsModal .list-group').append(newItemHtml);
			$('#newActionItemInput').val('');
			console.log('Added action item:', newItemText);
			// --- TODO: Add AJAX call here to save the item ---
		}
	});
	// Action Items - Remove Item (Event delegation for dynamically added items)
	$('#actionItemsModal .list-group').on('click', '.remove-item-btn', function () {
		$(this).closest('.list-group-item').remove();
		console.log('Removed action item.');
		// --- TODO: Add AJAX call here to remove the item ---
	});
	
	
	// Settings - Save (Simulation)
	$('#saveSettingsButton').on('click', function () {
		const mode = $('#defaultModeSelect').val();
		const theme = $('#themeSelect').val();
		const tone = $('input[name="personalityTone"]:checked').attr('id').replace('tone', ''); // e.g., Professional
		console.log('Saving settings:', {mode, theme, tone});
		// --- TODO: Add AJAX call here to save settings ---
		$('#settingsModal').modal('hide'); // Close modal on save
		alert('Settings saved (simulated)!');
	});
	
	// Summarize Content - Button Actions (Simulation)
	$('#summarizeWebButton, #summarizeFileButton, #summarizeTextButton').on('click', function () {
		const type = $(this).attr('id').replace('summarize', '').replace('Button', ''); // Web, File, Text
		let inputData;
		if (type === 'Web') inputData = $('#summarizeUrlInput').val();
		else if (type === 'File') inputData = $('#summarizeFileInput').prop('files')[0]?.name || 'No file selected';
		else if (type === 'Text') inputData = $('#summarizeTextInput').val().substring(0, 50) + '...'; // Preview text
		
		console.log(`Summarize ${type} requested with input:`, inputData);
		alert(`Summarizing ${type} (simulated)...`);
		// --- TODO: Add AJAX call here for summarization ---
		// Maybe show result in a dedicated area or close modal and show in chat
	});
	
	// Mode Dropdown Selection (Update Button Text) - Example of dropdown interaction
	$('#modeDropdownButton + .dropdown-menu .dropdown-item').on('click', function (e) {
		e.preventDefault();
		const selectedText = $(this).text().trim();
		$('#modeDropdownButton').text(selectedText);
		
		// Update active state and checkmark
		$('#modeDropdownButton + .dropdown-menu .dropdown-item').removeClass('active').find('i.bi-check').remove();
		$(this).addClass('active').prepend('<i class="bi bi-check me-2"></i>');
		
		console.log('Mode changed to:', selectedText);
		// You might trigger an update via AJAX if needed
	});
	
	// --- Dashboard Prompt Input Handling ---
	const dashboardPromptForm = $('#dashboard-prompt-form');
	const dashboardPromptInput = $('#dashboard-prompt-input');
	
	if (dashboardPromptForm.length && dashboardPromptInput.length) {
		dashboardPromptForm.on('submit', function (e) {
			e.preventDefault(); // Prevent default GET submission
			const promptText = dashboardPromptInput.val().trim();
			
			if (promptText) {
				// Construct the URL for the new chat page with the prompt as a query parameter
				const chatUrl = $(this).attr('action'); // Get base URL from form action
				const redirectUrl = chatUrl + '?prompt=' + encodeURIComponent(promptText);
				
				// Redirect the user
				window.location.href = redirectUrl;
			}
		});
		
		dashboardPromptInput.focus();
	}
	
	$('.sidebar .nav').on('click', '.delete-chat-btn', function (e) {
		e.preventDefault(); // Prevent link navigation
		e.stopPropagation(); // Stop event bubbling to the link
		
		const chatLinkElement = $(this).closest('a'); // Get the parent <a> tag
		const chatId = $(this).data('chat-id');
		const chatTitle = chatLinkElement.attr('title') || `Chat ID ${chatId}`;
		
		if (!chatId) {
			console.error('Could not find chat ID for deletion.');
			alert('Error: Could not determine which chat to delete.');
			return;
		}
		
		if (confirm(`Are you sure you want to delete the chat "${chatTitle}"? This cannot be undone.`)) {
			// Add visual indicator (optional)
			chatLinkElement.css('opacity', '0.5');
			
			$.ajax({
				url: `/api/chat/headers/${chatId}`, // Correct API endpoint
				method: 'DELETE',
				data: {
					_token: $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						chatLinkElement.fadeOut(300, function () {
							$(this).remove();
						});
						// Optional: Redirect if deleting the currently active chat
						// if (window.location.pathname.includes(`/chat/${chatId}`)) {
						//     window.location.href = '/'; // Redirect to dashboard
						// }
					} else {
						alert(data.error || 'Could not delete chat.');
						console.error("Chat deletion error:", data.error);
						chatLinkElement.css('opacity', '1'); // Restore opacity on error
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert('An error occurred while trying to delete the chat.');
					console.error("AJAX Chat Deletion Error:", textStatus, errorThrown);
					chatLinkElement.css('opacity', '1'); // Restore opacity on error
				}
			});
		}
	});
	
	
}); // End $(document).ready
