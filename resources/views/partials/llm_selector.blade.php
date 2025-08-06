{{-- MODIFIED: Rewritten with DaisyUI dropdown component --}}
<div class="dropdown">
    {{-- MODIFIED: Added the optional $buttonClass variable. This allows it to be full-width in the sidebar but auto-width in the header. --}}
    <div tabindex="0" role="button" class="btn btn-primary {{ $buttonClass ?? '' }}">
        {{-- MODIFIED: Replaced ID with a class for multiple instance support --}}
        <span class="selected-model-name btn-text">Smart Mode</span>
        <i class="bi bi-chevron-down"></i>
    </div>
    {{-- MODIFIED: Replaced ID with a class for multiple instance support --}}
    <ul tabindex="0" class="mode-dropdown-menu dropdown-content z-[1] menu flex-col p-2 shadow bg-base-100 rounded-box w-52 max-h-96" style="overflow-y: scroll; display: block">
        @if(isset($llms) && $llms->isNotEmpty())
            {{-- Loop through the LLMs provided by the controller --}}
            @foreach($llms as $llm)
                {{-- Each 'li' has a data-model-id, which your JS will use to get the selected model's ID. --}}
                <li data-model-id="{{ $llm->id }}">
                    <a data-display-name="{{ Str::limit($llm->name, 35) }}">
                        {{ Str::limit($llm->name, 35) }}
                    </a>
                </li>
            @endforeach
        @else
            {{-- Fallback if no models are loaded --}}
            <li><a>No models available</a></li>
        @endif
        <li><hr class="my-1"></li>
        <li><a id="settingsButtonFromDropdown"><i class="bi bi-gear-fill me-2"></i> Model Settings</a></li>
    </ul>
</div>
