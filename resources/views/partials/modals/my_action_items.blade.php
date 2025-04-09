<div class="modal fade" id="actionItemsModal" tabindex="-1" aria-labelledby="actionItemsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"> {{-- Added scrollable --}}
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="actionItemsModalLabel">My Action Items</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				@auth {{-- Show dynamic content only if logged in --}}
				<ul class="list-group list-group-flush" id="actionItemsList">
					{{-- Action items will be loaded here by JavaScript --}}
					<li>Loading action items...</li>
				</ul>
				@else {{-- Show placeholder if guest --}}
				<p class="text-muted text-center my-4">Please log in to manage your action items.</p>
				{{-- Or show the original static content as placeholder --}}
				<ul class="list-group list-group-flush visually-hidden"> {{-- Keep structure for layout but hide--}}
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action1-placeholder" disabled>
							<label class="form-check-label" for="action1-placeholder"> Placeholder item 1 </label>
							<br><small class="text-muted ms-4">Due: Some Date</small>
						</div>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action2-placeholder" checked disabled>
							<label class="form-check-label" for="action2-placeholder"> Placeholder item 2 (Done) </label>
							<br><small class="text-muted ms-4">Due: Another Date</small>
						</div>
					</li>
				</ul>
				@endauth
			</div>
			@auth {{-- Show add input only if logged in --}}
			<div class="modal-footer">
				<div class="input-group">
					<input type="text" class="form-control" placeholder="New action item..." id="newActionItemInput" aria-label="New action item">
					<button class="btn btn-dark" type="button" id="addActionItemButton">
						<i class="bi bi-plus-lg me-1"></i> Add
					</button>
				</div>
			</div>
			@endauth
		</div>
	</div>
</div>
