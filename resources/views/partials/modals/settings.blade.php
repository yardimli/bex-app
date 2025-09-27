{{-- MODIFIED: Use the new helper function to get the verified and grouped model list. --}}
@php
    $llms = \App\Helpers\MyHelper::getVerifiedGroupedModels();
@endphp
<dialog id="settingsModal" class="modal">
	<div class="modal-box">
		<form method="dialog">
			<button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
		</form>
		<h3 class="font-bold text-lg">Settings</h3>
		<div class="py-4 space-y-4">
			<div class="form-control w-full">
				<label class="label" for="defaultModeSelect">
					<span class="label-text">Default Model (for new chats):</span>
				</label>
                {{-- MODIFIED: This select now uses <optgroup> to display the grouped models --}}
                <select class="select select-bordered" id="defaultModeSelect">
                    <option value="openai/gpt-4o-mini">Smart Mode (Default: gpt-4o-mini)</option>
                    <option disabled>────────────────</option>
                    @if(!empty($llms))
                        @foreach($llms as $group)
                            <optgroup label="{{ $group['group'] }}">
                                @foreach($group['models'] as $model)
                                    <option value="{{ $model['id'] }}">{{ $model['name'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @else
                        <option disabled>No models available</option>
                    @endif
                </select>
			</div>

			<div class="form-control w-full">
				<label class="label" for="themeSelect">
					<span class="label-text">Theme:</span>
				</label>
				<select class="select select-bordered" id="themeSelect">
					<option value="light">Light</option>
					<option value="dark">Dark</option>
				</select>
			</div>

			<div class="form-control">
				<label class="label"><span class="label-text">Personality/Tone:</span></label>
				<div class="grid grid-cols-2 gap-x-4 gap-y-2">
					<label class="label cursor-pointer"><span class="label-text">Professional</span><input type="radio" name="personalityTone" class="radio radio-primary" value="professional" checked /></label>
					<label class="label cursor-pointer"><span class="label-text">Friendly</span><input type="radio" name="personalityTone" class="radio radio-primary" value="friendly" /></label>
					<label class="label cursor-pointer"><span class="label-text">Witty</span><input type="radio" name="personalityTone" class="radio radio-primary" value="witty" /></label>
					<label class="label cursor-pointer"><span class="label-text">Poetic</span><input type="radio" name="personalityTone" class="radio radio-primary" value="poetic" /></label>
					<label class="label cursor-pointer"><span class="label-text">Motivational</span><input type="radio" name="personalityTone" class="radio radio-primary" value="motivational" /></label>
					<label class="label cursor-pointer"><span class="label-text">Sarcastic</span><input type="radio" name="personalityTone" class="radio radio-primary" value="sarcastic" /></label>
				</div>
			</div>
		</div>
		<div class="modal-action">
			<button class="btn btn-primary" id="saveSettingsButton">Save Changes</button>
		</div>
	</div>
</dialog>