<dialog id="summarizeContentModal" class="modal modal-bottom sm:modal-middle">
	<div class="modal-box w-11/12 max-w-3xl">
		<h3 class="font-bold text-lg">Summarize Content</h3>
		
		<div class="py-4">
			<div role="tablist" class="tabs tabs-lifted">
				<input type="radio" name="summarize_tabs" role="tab" class="tab" aria-label="Web Page" checked />
				<div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
					<div class="form-control w-full">
						<label class="label" for="summarizeUrlInput"><span class="label-text">Enter URL to summarize</span></label>
						<input type="url" id="summarizeUrlInput" class="input input-bordered w-full" placeholder="https://example.com">
					</div>
					<button type="button" class="btn btn-primary w-full mt-4" id="summarizeWebButton"><i class="bi bi-globe me-2"></i>Summarize Web Page</button>
				</div>
				
				<input type="radio" name="summarize_tabs" role="tab" class="tab" aria-label="File" />
				<div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
					<div class="form-control w-full">
						<label class="label" for="summarizeFileInput"><span class="label-text">Upload a file (PDF, DOCX, TXT)</span></label>
						<input type="file" id="summarizeFileInput" class="file-input file-input-bordered w-full">
					</div>
					<button type="button" class="btn btn-primary w-full mt-4" id="summarizeFileButton"><i class="bi bi-file-earmark-arrow-up me-2"></i>Summarize File</button>
				</div>
				
				<input type="radio" name="summarize_tabs" role="tab" class="tab" aria-label="Text" />
				<div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
					<div class="form-control w-full">
						<label class="label" for="summarizeTextInput"><span class="label-text">Paste text to summarize</span></label>
						<textarea id="summarizeTextInput" class="textarea textarea-bordered h-32" placeholder="Paste text here..."></textarea>
					</div>
					<button type="button" class="btn btn-primary w-full mt-4" id="summarizeTextButton"><i class="bi bi-textarea-t me-2"></i>Summarize Text</button>
				</div>
			</div>
		</div>
		
		<form method="dialog">
			<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
		</form>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
