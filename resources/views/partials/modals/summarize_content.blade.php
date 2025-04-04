<div class="modal fade" id="summarizeContentModal" tabindex="-1" aria-labelledby="summarizeContentModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="summarizeContentModalLabel">Summarize Content</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs nav-fill" id="summarizeTab" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="summarize-web-tab" data-bs-toggle="tab" data-bs-target="#summarize-web-pane" type="button" role="tab" aria-controls="summarize-web-pane" aria-selected="true">Web Page</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="summarize-file-tab" data-bs-toggle="tab" data-bs-target="#summarize-file-pane" type="button" role="tab" aria-controls="summarize-file-pane" aria-selected="false">File</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="summarize-text-tab" data-bs-toggle="tab" data-bs-target="#summarize-text-pane" type="button" role="tab" aria-controls="summarize-text-pane" aria-selected="false">Text</button>
					</li>
				</ul>
				
				<!-- Tab panes -->
				<div class="tab-content pt-3" id="summarizeTabContent">
					<div class="tab-pane fade show active" id="summarize-web-pane" role="tabpanel" aria-labelledby="summarize-web-tab" tabindex="0">
						<div class="mb-3">
							<label for="summarizeUrlInput" class="form-label">Enter URL to summarize</label>
							<input type="url" class="form-control" id="summarizeUrlInput" placeholder="https://example.com">
						</div>
						<button type="button" class="btn btn-dark w-100" id="summarizeWebButton"><i class="bi bi-globe me-2"></i>Summarize Web Page</button>
					</div>
					<div class="tab-pane fade" id="summarize-file-pane" role="tabpanel" aria-labelledby="summarize-file-tab" tabindex="0">
						<div class="mb-3">
							<label for="summarizeFileInput" class="form-label">Upload a file (PDF, DOCX, TXT)</label>
							<input class="form-control" type="file" id="summarizeFileInput">
						</div>
						<button type="button" class="btn btn-dark w-100" id="summarizeFileButton"><i class="bi bi-file-earmark-arrow-up me-2"></i>Summarize File</button>
					</div>
					<div class="tab-pane fade" id="summarize-text-pane" role="tabpanel" aria-labelledby="summarize-text-tab" tabindex="0">
						<div class="mb-3">
							<label for="summarizeTextInput" class="form-label">Paste text to summarize</label>
							<textarea class="form-control" id="summarizeTextInput" rows="6"></textarea>
						</div>
						<button type="button" class="btn btn-dark w-100" id="summarizeTextButton"><i class="bi bi-textarea-t me-2"></i>Summarize Text</button>
					</div>
				</div>
			</div>
			<!-- Optional Footer for results?
			 <div class="modal-footer">
					<div id="summaryResultArea" class="w-100"></div>
			</div>
			 -->
		</div>
	</div>
</div>
