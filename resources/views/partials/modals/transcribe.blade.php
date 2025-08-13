{{-- MODIFIED: Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="transcribeModal" class="modal">
	<div class="modal-box w-11/12 max-w-5xl">
		<h3 class="font-bold text-lg">Transcribe Audio/Video</h3>
		
		<div class="py-4">
			
			{{-- MODIFIED: Replaced row/col with a responsive grid --}}
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
				{{-- MODIFIED: Replaced card with DaisyUI card --}}
				<div class="card bg-base-200 shadow-md text-center">
					<div class="card-body items-center">
						<i class="bi bi-upload text-4xl text-primary"></i>
						<h4 class="card-title">Open Files...</h4>
						<p class="text-sm text-base-content/70">Upload audio or video files</p>
						<div class="card-actions justify-center mt-2">
							<button class="btn btn-primary">Upload</button>
						</div>
					</div>
				</div>
				<div class="card bg-base-200 shadow-md text-center">
					<div class="card-body items-center">
						<i class="bi bi-mic-fill text-4xl text-error"></i>
						<h4 class="card-title">New Recording</h4>
						<p class="text-sm text-base-content/70">Start a new voice recording</p>
						<div class="card-actions justify-center mt-2">
							<button class="btn btn-error">Record</button>
						</div>
					</div>
				</div>
			</div>
			
			<div class="flex flex-col sm:flex-row gap-4 mt-6">
				<div class="form-control w-full">
					<label class="label"><span class="label-text">Input Language</span></label>
					<select class="select select-bordered" id="transcribeInputLanguage">
						<option selected>English</option>
						<option>Spanish</option>
						<option>French</option>
					</select>
				</div>
				<div class="form-control w-full">
					<label class="label"><span class="label-text">Quality</span></label>
					<select class="select select-bordered" id="transcribeQuality">
						<option>Small</option>
						<option selected>Large (V3)</option>
						<option>Whisper</option>
					</select>
				</div>
			</div>
		</div>
		
		<form method="dialog">
			<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
		</form>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
