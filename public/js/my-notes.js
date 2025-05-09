$(document).ready(function () {
	const notesModal = $('#myNotesModal');
	const notesList = $('#notesList');
	const noteForm = $('#noteForm');
	const noteIdInput = $('#noteId');
	const noteTitleInput = $('#noteTitleInput');
	const noteContentInput = $('#noteContentInput');
	const saveNoteButton = $('#saveNoteButton');
	const newNoteButton = $('#newNoteButton');
	const deleteNoteButton = $('#deleteNoteButton');
	const noteEditorTitle = $('#noteEditorTitle');
	const notesLoadingMsg = $('#notesLoadingMsg');
	const noteViewPlaceholder = $('#noteViewPlaceholder');
	const csrfToken = $('meta[name="csrf-token"]').attr('content');
	
	let currentEditNoteId = null;
	
	function showForm() {
		noteForm.show();
		noteViewPlaceholder.hide();
	}
	
	function showPlaceholder() {
		noteForm.hide();
		noteViewPlaceholder.show();
	}
	
	function resetForm() {
		currentEditNoteId = null;
		noteIdInput.val('');
		noteTitleInput.val('');
		noteContentInput.val('');
		noteEditorTitle.text('New Note');
		deleteNoteButton.hide();
		noteTitleInput.focus();
		notesList.find('.list-group-item.active').removeClass('active');
		showForm();
	}
	
	function renderNoteItem(note) {
		const safeTitle = $('<div>').text(note.title).html();
		return `
            <a href="#" class="list-group-item list-group-item-action" data-id="${note.id}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 text-truncate" style="max-width: 80%;">${safeTitle}</h6>
                    <small class="text-muted">${new Date(note.updated_at).toLocaleDateString()}</small>
                </div>
                ${note.content ? '<p class="mb-1 small text-muted text-truncate">' + $('<div>').text(note.content).html().substring(0, 50) + '...</p>' : ''}
            </a>`;
	}
	
	function loadNotes() {
		notesLoadingMsg.text('Loading notes...').show();
		notesList.empty(); // Clear previous items
		showPlaceholder();
		
		$.ajax({
			url: '/api/notes',
			method: 'GET',
			dataType: 'json',
			success: function (notes) {
				notesLoadingMsg.hide();
				if (notes.length > 0) {
					notes.forEach(note => {
						notesList.append(renderNoteItem(note));
					});
				} else {
					notesList.html('<p class="text-muted p-2">No notes found. Create one!</p>');
				}
				if (notes.length === 0 || !currentEditNoteId) {
					resetForm(); // Start with new note form if no notes or no active edit
				} else {
					// If there was an active edit, try to re-select it
					const activeItem = notesList.find(`.list-group-item[data-id="${currentEditNoteId}"]`);
					if (activeItem.length) {
						activeItem.trigger('click');
					} else {
						resetForm();
					}
				}
			},
			error: function (jqXHR) {
				notesLoadingMsg.text('Could not load notes.').show();
				console.error("Error loading notes:", jqXHR.responseText);
			}
		});
	}
	
	notesModal.on('show.bs.modal', function () {
		if (notesList.length) { // Check if user is logged in (elements exist)
			loadNotes();
		}
	});
	
	newNoteButton.on('click', function () {
		resetForm();
	});
	
	notesList.on('click', '.list-group-item', function (e) {
		e.preventDefault();
		const listItem = $(this);
		const noteId = listItem.data('id');
		
		notesList.find('.list-group-item.active').removeClass('active');
		listItem.addClass('active');
		
		currentEditNoteId = noteId;
		noteEditorTitle.text('Edit Note');
		deleteNoteButton.show();
		showForm();
		
		// Fetch full note details to populate form
		saveNoteButton.prop('disabled', true).html('Loading...');
		$.ajax({
			url: `/api/notes/${noteId}`,
			method: 'GET',
			dataType: 'json',
			success: function (note) {
				noteIdInput.val(note.id);
				noteTitleInput.val(note.title);
				noteContentInput.val(note.content);
			},
			error: function (jqXHR) {
				alert('Could not load note details.');
				console.error("Error loading note:", jqXHR.responseText);
				resetForm(); // Go back to new note state
			},
			complete: function () {
				saveNoteButton.prop('disabled', false).html('<i class="bi bi-save"></i> Save Note');
			}
		});
	});
	
	noteForm.on('submit', function (e) {
		e.preventDefault();
		console.log('Note form submission triggered.'); // Log 1: Handler starts
		
		const title = noteTitleInput.val().trim();
		const content = noteContentInput.val().trim();
		const noteId = noteIdInput.val();
		
		console.log('Note ID:', noteId); // Log 2: Note ID
		console.log('Title input raw value:', noteTitleInput.val()); // Log 3: Raw title
		console.log('Title to save:', title); // Log 4: Trimmed title
		console.log('Content to save:', content); // Log 5: Content
		
		if (!title) {
			console.error('Title is empty. Aborting save.'); // Log 6: Title validation failed
			alert('Please enter a title for the note.');
			noteTitleInput.focus();
			return;
		}
		
		console.log('Title is valid. Proceeding with AJAX.'); // Log 7: Title validation passed
		
		const ajaxData = {
			title: title,
			content: content,
			_token: csrfToken // Ensure csrfToken is defined and has a value
		};
		let ajaxUrl = '/api/notes';
		let ajaxMethod = 'POST';
		
		if (noteId) {
			ajaxUrl = `/api/notes/${noteId}`;
			ajaxMethod = 'PUT';
		}
		
		console.log('AJAX URL:', ajaxUrl); // Log 8: AJAX details
		console.log('AJAX Method:', ajaxMethod);
		console.log('AJAX Data:', ajaxData);
		
		saveNoteButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
		
		$.ajax({
			url: ajaxUrl,
			method: ajaxMethod,
			data: JSON.stringify(ajaxData),
			contentType: 'application/json',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			dataType: 'json',
			success: function (savedNote) {
				console.log('Note saved successfully:', savedNote); // Log 9: AJAX success
				currentEditNoteId = savedNote.id;
				loadNotes(); // Reload list to show changes/new item
			},
			error: function (jqXHR) {
				console.error('Error saving note via AJAX:', jqXHR.responseText); // Log 10: AJAX error
				alert('Could not save note. Error: ' + (jqXHR.responseJSON?.message || 'Please try again.'));
			},
			complete: function () {
				console.log('AJAX call complete.'); // Log 11: AJAX complete
				saveNoteButton.prop('disabled', false).html('<i class="bi bi-save"></i> Save Note');
			}
		});
	});
	
	deleteNoteButton.on('click', function () {
		const noteId = noteIdInput.val();
		if (!noteId) return;
		
		const noteTitle = noteTitleInput.val() || "this note";
		if (confirm(`Are you sure you want to delete "${noteTitle}"?`)) {
			deleteNoteButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
			$.ajax({
				url: `/api/notes/${noteId}`,
				method: 'DELETE',
				headers: {'X-CSRF-TOKEN': csrfToken},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						currentEditNoteId = null; // Clear current edit
						loadNotes(); // Reload list
					} else {
						alert(response.message || 'Could not delete note.');
					}
				},
				error: function (jqXHR) {
					alert('Could not delete note. Please try again.');
					console.error("Error deleting note:", jqXHR.responseText);
				},
				complete: function () {
					deleteNoteButton.prop('disabled', false).html('<i class="bi bi-trash"></i> Delete');
				}
			});
		}
	});
});
