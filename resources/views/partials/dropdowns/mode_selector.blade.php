{{-- bex-app/resources/views/partials/dropdowns/mode_selector.blade.php --}}
<div class="dropdown">
	<button class="btn btn-primary dropdown-toggle" type="button" id="modeDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
		<span id="selected-model-name">Smart Mode</span> {{-- Placeholder, JS will update --}}
	</button>
	<ul class="dropdown-menu" aria-labelledby="modeDropdownButton">
		{{-- Define the default mapping --}}
		<li><a class="dropdown-item" href="#" data-model-id="openai/gpt-4o-mini" data-display-name="Smart Mode (4o Mini)">Smart Mode (4o Mini)</a></li>
		<li><hr class="dropdown-divider"></li>
		<li><a class="dropdown-item" href="#" data-model-id="openai/gpt-4o-mini" data-display-name="GPT-4o Mini">OpenAI: GPT-4o Mini</a></li>
		<li><a class="dropdown-item" href="#" data-model-id="openai/o1-mini" data-display-name="O1 Mini">OpenAI: O1 Mini</a></li>
		<li><a class="dropdown-item" href="#" data-model-id="anthropic/claude-3.5-haiku" data-display-name="Claude 3.5 Haiku">Anthropic: Claude 3.5 Haiku</a></li>
		<li><a class="dropdown-item" href="#" data-model-id="google/gemini-2.0-flash-001" data-display-name="Gemini 2 Flash">Google: Gemini 2 Flash</a></li>
		<li><a class="dropdown-item" href="#" data-model-id="deepseek/deepseek-chat-v3-0324" data-display-name="Deepseek V3">Deepseek: V3 Chat</a></li>
	</ul>
</div>
