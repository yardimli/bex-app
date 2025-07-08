{{-- MODIFIED: Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="actionItemsModal" class="modal modal-bottom sm:modal-middle">
	<div class="modal-box">
		<h3 class="font-bold text-lg">My Action Items</h3>
		
		{{-- MODIFIED: Body content section --}}
		<div class="py-4">
			@auth {{-- Show dynamic content only if logged in --}}
			{{-- MODIFIED: Replaced list-group with a simple ul and custom styling via JS/CSS --}}
			<ul class="space-y-2" id="actionItemsList">
				{{-- Action items will be loaded here by JavaScript --}}
				<li>Loading action items...</li>
			</ul>
			@else {{-- Show placeholder if guest --}}
			<p class="text-base-content/70 text-center my-4">Please log in to manage your action items.</p>
			@endauth
		</div>
		
		@auth {{-- Show add input only if logged in --}}
		{{-- MODIFIED: Replaced modal-footer and input-group with DaisyUI join component --}}
		<div class="modal-action mt-4">
			<div class="join w-full">
				<input type="text" class="input input-bordered join-item w-full" placeholder="New action item..." id="newActionItemInput" aria-label="New action item">
				<button class="btn btn-primary join-item" type="button" id="addActionItemButton">
					<i class="bi bi-plus-lg me-1"></i> Add
				</button>
			</div>
		</div>
		@endauth
		
		{{-- MODIFIED: Added a standard close button for accessibility --}}
		<div class="modal-action">
			<form method="dialog">
				<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
			</form>
		</div>
	</div>
	{{-- MODIFIED: Allow closing by clicking backdrop --}}
	<form method="dialog" class="modal-backdrop">
		<button>close</button>
	</form>
</dialog>
