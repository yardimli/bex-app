{{-- MODIFIED: Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="recentMeetingsModal" class="modal modal-bottom sm:modal-middle">
	<div class="modal-box w-11/12 max-w-4xl">
		<h3 class="font-bold text-lg">Recent Meetings</h3>
		
		<div class="py-4">
			{{-- MODIFIED: Replaced row/col with flexbox --}}
			<div class="flex flex-col md:flex-row gap-4">
				<div class="md:w-2/5">
					{{-- MODIFIED: Replaced list-group with DaisyUI menu --}}
					<ul class="menu bg-base-200 rounded-box">
						<li><a data-id="q4-financial"><strong>Q4 Financial Review</strong><span class="text-xs text-base-content/60">2024-12-03</span></a></li>
						<li><a data-id="omega-kickoff"><strong>Project Omega Kickoff</strong><span class="text-xs text-base-content/60">2024-12-02</span></a></li>
						<li><a data-id="hr-policy"><strong>HR Policy Update</strong><span class="text-xs text-base-content/60">2024-12-01</span></a></li>
					</ul>
				</div>
				<div class="md:w-3/5 flex items-center justify-center bg-base-200 rounded-box min-h-48">
					<span class="text-base-content/60">Select a meeting to view details</span>
				</div>
			</div>
		</div>
		
		<form method="dialog">
			<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
		</form>
	</div>
	<form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
