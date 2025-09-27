@php
    // MODIFIED: The $llms variable is now expected to be the verified, grouped list from the controller.
    // If it's not passed for some reason, we'll fetch it as a fallback.
    $llms = $llms ?? \App\Helpers\MyHelper::getVerifiedGroupedModels();
@endphp
<div class="dropdown">
    <div tabindex="0" role="button" class="btn btn-primary {{ $buttonClass ?? '' }}">
        <span class="selected-model-name btn-text">Smart Mode</span>
        <i class="bi bi-chevron-down"></i>
    </div>
    <ul tabindex="0" class="mode-dropdown-menu dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-80 max-h-96 overflow-y-auto flex-nowrap">
        {{-- MODIFIED: This section now iterates over groups and their models. --}}
        @if(!empty($llms))
            @foreach($llms as $group)
                {{-- Render the group title --}}
                <li class="menu-title">
                    <span>{{ $group['group'] }}</span>
                </li>
                {{-- Render the models within the group --}}
                @foreach($group['models'] as $model)
                    <li data-model-id="{{ $model['id'] }}">
                        <a data-display-name="{{ $model['name'] }}">{{ $model['name'] }}</a>
                    </li>
                @endforeach
                {{-- Add a divider after each group, except for the last one --}}
                @if(!$loop->last)
                    <li><hr class="my-1"></li>
                @endif
            @endforeach
        @else
            {{-- Fallback if no models are available --}}
            <li><a>No models available</a></li>
        @endif
        {{-- End of modified section --}}
        <li><hr class="my-1"></li>
        <li><a id="settingsButtonFromDropdown"><i class="bi bi-gear-fill me-2"></i> Model Settings</a></li>
    </ul>
</div>
