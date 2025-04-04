<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="settingsModalLabel">Settings</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label for="defaultModeSelect" class="form-label">Default Mode:</label>
					<select class="form-select" id="defaultModeSelect">
						<option selected>Smart Mode</option>
						<option>Llama 3.2</option>
						<option>Claude Sonnet</option>
						<option>Gemini Ultra</option>
						<option>OpenAI O1</option>
					</select>
				</div>
				
				<div class="mb-3">
					<label for="themeSelect" class="form-label">Theme:</label>
					<select class="form-select" id="themeSelect">
						<option selected>Light</option>
						<option>Dark</option>
					</select>
				</div>
				
				<div class="mb-3">
					<label class="form-label d-block">Personality/Tone:</label>
					<div class="row">
						<div class="col">
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneProfessional" checked>
								<label class="form-check-label" for="toneProfessional">Professional</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneWitty">
								<label class="form-check-label" for="toneWitty">Witty</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneMotivational">
								<label class="form-check-label" for="toneMotivational">Motivational</label>
							</div>
						</div>
						<div class="col">
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneFriendly">
								<label class="form-check-label" for="toneFriendly">Friendly</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="tonePoetic">
								<label class="form-check-label" for="tonePoetic">Poetic</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneSarcastic">
								<label class="form-check-label" for="toneSarcastic">Sarcastic</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="saveSettingsButton">Save Changes</button>
			</div>
		</div>
	</div>
</div>
