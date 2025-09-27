<dialog id="myRecordingsModal" class="modal modal-bottom sm:modal-middle">
	<div class="modal-box w-11/12 max-w-4xl">
		<h3 class="font-bold text-lg">My Recordings</h3>
		
		<div class="py-4">
			<div class="flex flex-col md:flex-row gap-4">
				<div class="md:w-2/5">
					<ul class="menu bg-base-200 rounded-box">
						<li><a data-id="alpha-team"><strong>Team Meeting - Project Alpha</strong><span class="text-xs text-base-content/60">2024-12-05 - 45:30</span></a></li>
						<li><a data-id="client-feature"><strong>Client Call - Feature Discussion</strong><span class="text-xs text-base-content/60">2024-12-04 - 32:15</span></a></li>
						<li><a data-id="brainstorm-sess"><strong>Brainstorming Session</strong><span class="text-xs text-base-content/60">2024-12-03 - 58:42</span></a></li>
						<li><a data-id="personal-review"><strong>Personal Notes - Week Review</strong><span class="text-xs text-base-content/60">2024-12-02 - 12:07</span></a></li>
					</ul>
				</div>
				<div class="md:w-3/5 flex items-center justify-center bg-base-200 rounded-box min-h-48">
					<span class="text-base-content/60">Select a recording to play</span>
				</div>
			</div>
		</div>
		
		<div class="modal-action mt-2">
			<button class="btn btn-primary w-full"><i class="bi bi-mic-fill me-2"></i>Start New Recording</button>
		</div>
		
		<form method="dialog">
			<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
		</form>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
