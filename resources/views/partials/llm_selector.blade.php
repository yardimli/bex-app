@php
    $llms = \App\Helpers\MyHelper::getLlmList();
@endphp
<div class="dropdown">
    <div tabindex="0" role="button" class="btn btn-primary {{ $buttonClass ?? '' }}">
        <span class="selected-model-name btn-text">Smart Mode</span>
        <i class="bi bi-chevron-down"></i>
    </div>
    <ul tabindex="0" class="mode-dropdown-menu dropdown-content z-[1] menu flex-col p-2 shadow bg-base-100 rounded-box w-96 max-h-96" style="overflow-y: scroll; display: block">
        <li data-model-id="openai/gpt-4o-mini"><a data-display-name="Smart Mode (Default : gpt-4o-mini)">Smart Mode (Default : gpt-4o-mini)</a></li>
        <li><hr class="my-1"></li>
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
