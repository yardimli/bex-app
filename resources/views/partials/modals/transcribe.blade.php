<div class="modal fade" id="transcribeModal" tabindex="-1" aria-labelledby="transcribeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="transcribeModalLabel">Transcribe Audio/Video</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="input-group mb-4">
					<span class="input-group-text"><i class="bi bi-link-45deg icon-color"></i></span>
					<input type="text" class="form-control form-control-lg" placeholder="Enter YouTube, Audio or Video File URL...">
					<button class="btn btn-outline-secondary" type="button">Go</button>
				</div>
				
				<div class="row g-3">
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-upload fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">Open Files...</h5>
								<p class="card-text text-muted"><small>Upload audio or video files</small></p>
								<button class="btn btn-primary mt-auto">Upload</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-mic-fill fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">New Recording</h5>
								<p class="card-text text-muted"><small>Start a new voice recording</small></p>
								<button class="btn btn-danger mt-auto">Record</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-pc-display-horizontal fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">Record App Audio</h5>
								<p class="card-text text-muted"><small>Record system audio</small></p>
								<button class="btn btn-secondary mt-auto">Record System</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-globe fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">Global</h5>
								<p class="card-text text-muted"><small>Access global transcription settings</small></p>
								<button class="btn btn-secondary mt-auto">Settings</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-mic fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">Transcribe Podcast</h5>
								<p class="card-text text-muted"><small>Transcribe podcast episodes</small></p>
								<button class="btn btn-secondary mt-auto">Podcast</button>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card h-100 text-center">
							<div class="card-body d-flex flex-column justify-content-center">
								<i class="bi bi-sliders fs-1 mb-2 icon-color"></i>
								<h5 class="card-title">Manage Models</h5>
								<p class="card-text text-muted"><small>Configure transcription models</small></p>
								<button class="btn btn-secondary mt-auto">Configure</button>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row mt-4 align-items-end">
					<div class="col-md-6">
						<label for="transcribeInputLanguage" class="form-label">Input Language</label>
						<select class="form-select" id="transcribeInputLanguage">
							<option selected>English</option>
							<option>Spanish</option>
							<option>French</option>
							{/* <!-- Add more languages --> */}
						</select>
					</div>
					<div class="col-md-6">
						<label for="transcribeQuality" class="form-label">Quality</label>
						<select class="form-select" id="transcribeQuality">
							<option>Small</option>
							<option selected>Large (V3)</option>
							<option>Whisper</option>
							{/* <!-- Add more models/quality --> */}
						</select>
					</div>
				</div>
			
			</div>
			<!-- Optional footer
			<div class="modal-footer">
				 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		 </div>
			-->
		</div>
	</div>
</div>
