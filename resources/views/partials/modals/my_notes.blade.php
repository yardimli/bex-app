<div class="modal fade" id="myNotesModal" tabindex="-1" aria-labelledby="myNotesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"> {{-- Larger modal --}}
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myNotesModalLabel">My Notes</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				@auth
					<div class="row">
						<div class="col-md-4 border-end">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<h6 class="mb-0">Notes List</h6>
								<button class="btn btn-sm btn-dark" id="newNoteButton"><i class="bi bi-plus-lg"></i> New Note</button>
							</div>
							<div class="list-group list-group-flush" id="notesList" style="max-height: 60vh; overflow-y: auto;">
								{{-- Notes will be loaded here by JavaScript --}}
								<p class="text-muted p-2" id="notesLoadingMsg">Loading notes...</p>
							</div>
						</div>
						<div class="col-md-8">
							<h6 id="noteEditorTitle">Note Details</h6>
							<form id="noteForm">
								<input type="hidden" id="noteId" value="">
								<div class="mb-3">
									<label for="noteTitleInput" class="form-label">Title</label>
									<input type="text" class="form-control" id="noteTitleInput" placeholder="Enter note title" required>
								</div>
								<div class="mb-3">
									<label for="noteContentInput" class="form-label">Content</label>
									<textarea class="form-control" id="noteContentInput" rows="10" placeholder="Enter note content"></textarea>
								</div>
								<div class="d-flex justify-content-end">
									<button type="button" class="btn btn-outline-danger me-auto" id="deleteNoteButton" style="display: none;"><i class="bi bi-trash"></i> Delete</button>
									<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
									<button type="submit" class="btn btn-primary" id="saveNoteButton"><i class="bi bi-save"></i> Save Note</button>
								</div>
							</form>
							<div id="noteViewPlaceholder" class="text-center text-muted mt-5" style="display: none;">
								<p><i class="bi bi-journal-text fs-1"></i></p>
								<p>Select a note from the list to view or edit, or create a new one.</p>
							</div>
						</div>
					</div>
				@else
					<p class="text-muted text-center my-4">Please log in to manage your notes.</p>
				@endauth
			</div>
		</div>
	</div>
</div>
