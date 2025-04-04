<div class="modal fade" id="actionItemsModal" tabindex="-1" aria-labelledby="actionItemsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="actionItemsModalLabel">My Action Items</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<ul class="list-group list-group-flush">
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action1">
							<label class="form-check-label" for="action1">
								Prepare Q4 financial report
							</label>
							<br><small class="text-muted ms-4">Due: 2024-12-15</small>
						</div>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action2" checked>
							<label class="form-check-label" for="action2">
								Schedule team building event
							</label>
							<br><small class="text-muted ms-4">Due: 2024-12-10</small>
						</div>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action3">
							<label class="form-check-label" for="action3">
								Review and approve marketing campaign
							</label>
							<br><small class="text-muted ms-4">Due: 2024-12-20</small>
						</div>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<input class="form-check-input me-2" type="checkbox" value="" id="action4">
							<label class="form-check-label" for="action4">
								Update project management software
							</label>
							<br><small class="text-muted ms-4">Due: 2024-12-18</small>
						</div>
					</li>
					<!-- Add more items -->
				</ul>
			</div>
			<div class="modal-footer">
				<div class="input-group">
					<input type="text" class="form-control" placeholder="New action item..." id="newActionItemInput">
					<button class="btn btn-dark" type="button" id="addActionItemButton"><i class="bi bi-plus-lg me-1"></i> Add</button>
				</div>
			</div>
		</div>
	</div>
</div>
