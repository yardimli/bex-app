{{-- MODIFIED: Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="myNotesModal" class="modal">
	{{-- MODIFIED: Added max-w-6xl for an XL size --}}
	<div class="modal-box w-11/12 max-w-6xl">
		<h3 class="font-bold text-lg">My Notes</h3>
		
		<div class="py-4">
			@auth
				{{-- MODIFIED: Replaced Bootstrap row/col with Flexbox --}}
				<div class="flex flex-col md:flex-row gap-4">
					{{-- MODIFIED: Notes list column --}}
					<div class="md:w-1/3 border-r-0 md:border-r border-base-300 pr-0 md:pr-4">
						<div class="flex justify-between items-center mb-2">
							<h4 class="font-semibold">Notes List</h4>
							<button class="btn btn-sm btn-primary" id="newNoteButton"><i class="bi bi-plus-lg"></i> New</button>
						</div>
						{{-- MODIFIED: Replaced list-group with DaisyUI menu --}}
						<ul class="menu bg-base-200 rounded-box h-96 overflow-y-auto" id="notesList">
							<li id="notesLoadingMsg"><a>Loading notes...</a></li>
						</ul>
					</div>
					{{-- MODIFIED: Note editor column --}}
					<div class="md:w-2/3">
						<h4 id="noteEditorTitle" class="font-semibold">Note Details</h4>
						<form id="noteForm" class="mt-2">
							<input type="hidden" id="noteId" value="">
							<div class="form-control w-full mb-3">
								<label class="label"><span class="label-text">Title</span></label>
								<input type="text" class="input input-bordered w-full" id="noteTitleInput" placeholder="Enter note title" required>
							</div>
							<div class="form-control w-full mb-3">
								<label class="label"><span class="label-text">Content</span></label>
								<textarea class="textarea textarea-bordered h-48" id="noteContentInput" placeholder="Enter note content"></textarea>
							</div>
							<div class="flex justify-end items-center gap-2 mt-4">
								<button type="button" class="btn btn-error mr-auto" id="deleteNoteButton" style="display: none;"><i class="bi bi-trash"></i> Delete</button>
								<form method="dialog"><button class="btn btn-ghost">Cancel</button></form>
								<button type="submit" class="btn btn-primary" id="saveNoteButton"><i class="bi bi-save"></i> Save Note</button>
							</div>
						</form>
						{{-- MODIFIED: Placeholder view --}}
						<div id="noteViewPlaceholder" class="text-center text-base-content/60 mt-5" style="display: none;">
							<i class="bi bi-journal-text text-6xl"></i>
							<p>Select a note to view or edit, or create a new one.</p>
						</div>
					</div>
				</div>
			@else
				<p class="text-base-content/70 text-center my-4">Please log in to manage your notes.</p>
			@endauth
		</div>
		
		<div class="modal-action">
			<form method="dialog">
				<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
			</form>
		</div>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
