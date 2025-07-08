// public/js/my-notes.js:

$(document).ready(function () {
	// MODIFIED: Get dialog element
	const notesModal = document.getElementById('myNotesModal');
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
		notesList.find('a.active').removeClass('active');
		showForm();
	}
	
	function renderNoteItem(note) {
		const safeTitle = $('<div>').text(note.title).html();
		// MODIFIED: Replaced list-group classes with DaisyUI menu structure
		return `
            <li>
                <a href="#" data-id="${note.id}">
                    <div class="flex-grow">
                        <div class="flex justify-between w-full">
                            <h6 class="font-semibold text-truncate" style="max-width: 80%;">${safeTitle}</h6>
                            <small class="text-base-content/60">${new Date(note.updated_at).toLocaleDateString()}</small>
                        </div>
                        ${note.content ? '<p class="text-sm text-base-content/60 text-truncate">' + $('<div>').text(note.content).html().substring(0, 50) + '...</p>' : ''}
                    </div>
                </a>
            </li>
        `;
	}
	
	function loadNotes() {
		notesLoadingMsg.show().find('a').text('Loading notes...');
		notesList.empty().append(notesLoadingMsg);
		showPlaceholder();
		
		$.ajax({
			url: '/api/notes',
			method: 'GET',
			dataType: 'json',
			success: function (notes) {
				notesList.empty(); // Clear loading message
				if (notes.length > 0) {
					notes.forEach(note => {
						notesList.append(renderNoteItem(note));
					});
				} else {
					notesList.html('<li><a class="text-base-content/60">No notes found. Create one!</a></li>');
				}
				if (notes.length === 0 || !currentEditNoteId) {
					resetForm();
				} else {
					const activeItem = notesList.find(`a[data-id="${currentEditNoteId}"]`);
					if (activeItem.length) {
						activeItem.trigger('click');
					} else {
						resetForm();
					}
				}
			},
			error: function (jqXHR) {
				notesList.html(`<li><a class="text-error">Could not load notes.</a></li>`);
				console.error("Error loading notes:", jqXHR.responseText);
			}
		});
	}
	
	// MODIFIED: Changed from Bootstrap event to a click handler on the trigger button
	$('#myNotesButton').on('click', function() {
		if (notesList.length) {
			loadNotes();
		}
		// MODIFIED: Use the native showModal() method
		notesModal.showModal();
	});
	
	newNoteButton.on('click', function () {
		resetForm();
	});
	
	notesList.on('click', 'a', function (e) {
		e.preventDefault();
		const listItem = $(this);
		const noteId = listItem.data('id');
		
		notesList.find('a.active').removeClass('active');
		listItem.addClass('active');
		
		currentEditNoteId = noteId;
		noteEditorTitle.text('Edit Note');
		deleteNoteButton.show();
		showForm();
		
		// MODIFIED: Use DaisyUI spinner
		saveNoteButton.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Loading...');
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
				resetForm();
			},
			complete: function () {
				saveNoteButton.prop('disabled', false).html('<i class="bi bi-save"></i> Save Note');
			}
		});
	});
	
	noteForm.on('submit', function (e) {
		e.preventDefault();
		const title = noteTitleInput.val().trim();
		const content = noteContentInput.val().trim();
		const noteId = noteIdInput.val();
		
		if (!title) {
			alert('Please enter a title for the note.');
			noteTitleInput.focus();
			return;
		}
		
		const ajaxData = { title: title, content: content, _token: csrfToken };
		let ajaxUrl = '/api/notes';
		let ajaxMethod = 'POST';
		
		if (noteId) {
			ajaxUrl = `/api/notes/${noteId}`;
			ajaxMethod = 'PUT';
		}
		
		// MODIFIED: Use DaisyUI spinner
		saveNoteButton.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Saving...');
		
		$.ajax({
			url: ajaxUrl,
			method: ajaxMethod,
			data: JSON.stringify(ajaxData),
			contentType: 'application/json',
			headers: { 'X-CSRF-TOKEN': csrfToken },
			dataType: 'json',
			success: function (savedNote) {
				currentEditNoteId = savedNote.id;
				loadNotes();
			},
			error: function (jqXHR) {
				alert('Could not save note. Error: ' + (jqXHR.responseJSON?.message || 'Please try again.'));
			},
			complete: function () {
				saveNoteButton.prop('disabled', false).html('<i class="bi bi-save"></i> Save Note');
			}
		});
	});
	
	deleteNoteButton.on('click', function () {
		const noteId = noteIdInput.val();
		if (!noteId) return;
		
		const noteTitle = noteTitleInput.val() || "this note";
		if (confirm(`Are you sure you want to delete "${noteTitle}"?`)) {
			// MODIFIED: Use DaisyUI spinner
			deleteNoteButton.prop('disabled', true).html('<span class="loading loading-spinner loading-sm"></span> Deleting...');
			$.ajax({
				url: `/api/notes/${noteId}`,
				method: 'DELETE',
				headers: {'X-CSRF-TOKEN': csrfToken},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						currentEditNoteId = null;
						loadNotes();
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
