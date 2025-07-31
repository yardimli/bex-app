@extends('layouts.app')

@push('styles')
    <style>
        #mentions-list > li.mention-active > a {
            background-color: darkgray;
            color: hsl(var(--pc));
        }
    </style>
@endpush

@section('content')
    <div class="p-2 md:p-4 flex flex-col h-full gap-2 md:gap-4">
        @include('partials.page_header')

        <div class="bg-base-100 rounded-box shadow-sm flex-grow flex flex-col p-4 min-h-0">
            {{-- Chat History --}}
            <div id="chat-history-area" class="chat-history flex-grow space-y-4">
                @if($messages->isEmpty())
                    <div class="text-center text-base-content/60 mt-5" id="empty-conversation">
                        Start the group conversation by typing below.
                    </div>
                @else
                    @foreach ($messages as $message)
                        @php

                            $isAssistant = $message->role === 'assistant';
                            $isCurrentUser = !$isAssistant && $message->user_id === auth()->id();
                            $alignment = $isAssistant ? 'chat-start' : 'chat-end';
                            $bubbleColor = $isCurrentUser ? 'chat-bubble-primary' : '';
                            $senderName = $isAssistant ? 'Bex' : ($message->user ? $message->user->name : 'Unknown User');
                            $processedContent = e($message->content); // Escape first
            if (isset($participants) && $participants->isNotEmpty()) {
                $participantNames = $participants->pluck('name');
                if ($participantNames->isNotEmpty()) {
                    $escapedNames = $participantNames->map(function ($name) {
                        return preg_quote($name, '/');
                    });
                    // Match @ followed by a name from the list, ensuring it's a whole word
                    $mentionRegex = '/@(' . $escapedNames->join('|') . ')\b/i';
                    $processedContent = preg_replace($mentionRegex, '<strong><u>$0</u></strong>', $processedContent);
                }
            }
                        @endphp
                        <div class="chat {{ $alignment }}" id="message-{{ $message->id }}" data-message-content="{!! e($message->content) !!}">
                            <div class="chat-bubble {{ $bubbleColor }} relative">
                                @if($message->files->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        @foreach($message->files as $file)
                                            <a href="{{ route('api.files.download', $file) }}" class="badge badge-outline" title="Download {{ $file->original_filename }}">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> {{ Str::limit($file->original_filename, 25) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                    {!! $processedContent !!}
                                @if ($isCurrentUser)
                                    <button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn" title="Delete pair" data-message-id="{{ $message->id }}">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="chat-footer opacity-50 flex items-center gap-2 mt-1">
                                <span class="text-xs font-semibold">{{ $senderName }}</span>
                                <time class="text-xs">{{ $message->created_at->format('H:i') }}</time>
                                <div class="flex-grow"></div>
                                @if ($isAssistant)
                                    <button class="btn btn-ghost btn-xs copy-btn" title="Copy text" data-message-id="{{ $message->id }}"><i class="bi bi-clipboard"></i></button>
                                    <button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud" data-message-id="{{ $message->id }}">
                                        <i class="bi bi-play-circle"></i>
                                        <span class="loading loading-spinner loading-xs" style="display: none;"></span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Loading Indicator --}}
            <div id="chat-loader" class="text-center p-2 hidden">
                <span class="loading loading-dots loading-md"></span>
            </div>

            {{-- Message Input Area --}}
            <div class="w-full pt-4">
                <form id="chat-input-form" class="w-full max-w-4xl mx-auto">
                    @csrf
                    <input type="hidden" id="team_id" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" id="group_chat_header_id" name="group_chat_header_id" value="{{ $activeChat?->id }}">
                    <input type="hidden" id="current_user_id" value="{{ auth()->id() }}">
                    <input type="hidden" id="attached-files-input" name="attached_files">
                    <div id="file-pills-container" class="flex flex-wrap gap-2 mb-2"></div>
                    <div class="form-control relative">
                        <div class="join w-full">
                            <button type="button" class="btn join-item" id="attach-file-btn"><i class="bi bi-paperclip text-xl"></i></button>
                            <textarea class="textarea textarea-bordered join-item w-full" id="message-input-field" name="message" placeholder="Message {{ $team->name }}... (use @ to mention)" rows="1" style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary join-item" id="send-message-button" title="Send">
                                <i class="bi bi-send-fill text-xl"></i>
                            </button>
                        </div>
                        <div id="mentions-dropdown" class="absolute bottom-full mb-1 w-full max-w-xs bg-base-200 rounded-box shadow-lg z-10 hidden">
                            <ul id="mentions-list" class="menu p-2 max-h-48 overflow-y-auto">
                                {{-- Mention suggestions will be populated by JS --}}
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const groupParticipants = @json($mentionableParticipants->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->all());
    </script>
    <script src="{{ asset('js/group-chat.js') }}"></script>
@endpush
