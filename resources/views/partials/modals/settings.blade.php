<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="settingsModalLabel">Settings</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label for="defaultModeSelect" class="form-label">Default Model (for new chats):</label>
					<select class="form-select" id="defaultModeSelect">
						{{-- Options should mirror mode_selector.blade.php, using model ID as value --}}
						{{-- Define the default mapping --}}
						<option value="openai/gpt-4o-mini">Smart Mode (4o Mini)</option>
						{{-- Separator isn't applicable in <select>, maybe add comments or organize --}}
						<option value="openai/gpt-4o-mini">OpenAI: GPT-4o Mini</option>
						<option value="openai/o1-mini">OpenAI: O1 Mini</option>
						<option value="anthropic/claude-3.5-haiku">Anthropic: Claude 3.5 Haiku</option>
						<option value="google/gemini-2.0-flash-001">Google: Gemini 2 Flash</option>
						<option value="deepseek/deepseek-chat-v3-0324">Deepseek: V3 Chat</option>
						{{-- Add any other models from mode_selector here --}}
					</select>
					<small class="form-text text-muted">This model will be used by default when you start a new chat.</small>
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
								<input class="form-check-input" type="radio" name="personalityTone" id="toneProfessional" value="professional" checked>
								<label class="form-check-label" for="toneProfessional">Professional</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneWitty" value="witty">
								<label class="form-check-label" for="toneWitty">Witty</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneMotivational" value="motivational">
								<label class="form-check-label" for="toneMotivational">Motivational</label>
							</div>
						</div>
						<div class="col">
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneFriendly" value="friendly">
								<label class="form-check-label" for="toneFriendly">Friendly</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="tonePoetic" value="poetic">
								<label class="form-check-label" for="tonePoetic">Poetic</label>
							</div>
							<div class="form-check mb-2">
								<input class="form-check-input" type="radio" name="personalityTone" id="toneSarcastic" value="sarcastic">
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
