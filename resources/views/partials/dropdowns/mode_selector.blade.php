{{-- MODIFIED: Rewritten with DaisyUI dropdown component --}}
<div class="dropdown">
	<div tabindex="0" role="button" class="btn btn-primary">
		<span id="selected-model-name" class="btn-text">Smart Mode</span>
		<i class="bi bi-chevron-down"></i>
	</div>
	{{-- MODIFIED: Added an ID for easier and more reliable JS selection --}}
	<ul tabindex="0" id="mode-dropdown-menu" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
		{{-- The 'bordered' class will be used by JS to show the active item --}}
		<li data-model-id="openai/gpt-4o-mini"><a data-display-name="Smart Mode (4o Mini)">Smart Mode (4o Mini)</a></li>
		<li><hr class="my-1"></li>
		<li data-model-id="openai/gpt-4o-mini"><a data-display-name="GPT-4o Mini">OpenAI: GPT-4o Mini</a></li>
		<li data-model-id="openai/o1-mini"><a data-display-name="O1 Mini">OpenAI: O1 Mini</a></li>
		<li data-model-id="anthropic/claude-3.5-haiku"><a data-display-name="Claude 3.5 Haiku">Anthropic: Claude 3.5 Haiku</a></li>
		<li data-model-id="google/gemini-2.0-flash-001"><a data-display-name="Gemini 2 Flash">Google: Gemini 2 Flash</a></li>
		<li data-model-id="deepseek/deepseek-chat-v3-0324"><a data-display-name="Deepseek V3">Deepseek: V3 Chat</a></li>
		<li><hr class="my-1"></li>
		<li><a id="settingsButtonFromDropdown"><i class="bi bi-gear-fill me-2"></i> Model Settings</a></li>
	</ul>
</div>
