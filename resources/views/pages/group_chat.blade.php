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
        <div class="bg-base-200 rounded-box shadow-sm flex-grow flex flex-col min-h-0">
            <div id="chat-history-area" class="chat-history flex-grow space-y-6 p-4">
                @if($messages->isEmpty())
                    <div class="text-center text-base-content/60 mt-5" id="empty-conversation">
                        Start the group conversation by typing below.
                    </div>
                @else
                    @foreach ($messages as $message)
                        @php
                            $isAssistant = $message->role === 'assistant';
                            $isCurrentUser = !$isAssistant && $message->user_id === auth()->id();
                            $alignment = $isAssistant ? 'chat-start' : ($isCurrentUser ? 'chat-end' : 'chat-start');

                            // --- START: NEW AVATAR & SENDER LOGIC ---
                            $senderName = $isAssistant ? 'Bex' : ($message->user ? $message->user->name : 'Unknown User');
                            $avatarInitials = 'BX';
                            $avatarColorClass = 'bg-neutral text-neutral-content'; // Default for Bex AI

                            if (!$isAssistant && $message->user) {
                                $name = $message->user->name;
                                $words = explode(" ", $name);
                                $initials = "";
                                if (count($words) >= 2) {
                                    $initials .= strtoupper(substr($words[0], 0, 1));
                                    $initials .= strtoupper(substr(end($words), 0, 1));
                                } elseif (strlen($name) > 1) {
                                    $initials = strtoupper(substr($name, 0, 2));
                                } else {
                                    $initials = "??";
                                }
                                $avatarInitials = $initials;

                                // Consistent color based on user ID
                                $colors = ['bg-sky-500', 'bg-amber-500', 'bg-rose-500', 'bg-violet-500', 'bg-emerald-500', 'bg-red-500'];
                                $hash = crc32($message->user->id);
                                $colorIndex = abs($hash) % count($colors);
                                $avatarColorClass = $colors[$colorIndex] . ' text-white';
                            }
                            // --- END: NEW AVATAR & SENDER LOGIC ---

                            // --- MODIFIED: BUBBLE & MENTION STYLING ---
                            $bubbleColor = $isCurrentUser ? 'chat-bubble-primary' : 'bg-white border border-base-200 text-base-content';
            $processedContent = e($message->content); // Escape first
            if (isset($participants) && $participants->isNotEmpty()) {
                $participantNames = $participants->pluck('name')->push('Bex');

                if ($participantNames->isNotEmpty()) {
                    $escapedNames = $participantNames->map(function ($name) { return preg_quote($name, '/'); });
                    $mentionRegex = '/@(' . $escapedNames->join('|') . ')\b/i';
                    $replacement = '<span class="bg-green-100 text-green-800 font-semibold rounded px-1 py-0.5">$0</span>';
                    $processedContent = preg_replace($mentionRegex, $replacement, $processedContent);
                }
            }
                        @endphp
                        {{-- START: MODIFIED MESSAGE STRUCTURE --}}
                        <div class="chat {{ $alignment }}" id="message-{{ $message->id }}"
                             data-message-content="{!! e($message->content) !!}">
                            <div class="chat-image avatar placeholder">
                                <div class="w-10 rounded-full {{ $avatarColorClass }}">
                                    <span class="text-lg">{{ $avatarInitials }}</span>
                                </div>
                            </div>
                            <div class="chat-header">
                                {{ $senderName }}
                                <time class="text-xs opacity-50 ml-1">{{ $message->created_at->format('h:i A') }}</time>
                            </div>
                            <div class="chat-bubble {{ $bubbleColor }} relative">
                                @if($message->files->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        @foreach($message->files as $file)
                                            <a href="{{ route('api.files.download', $file) }}"
                                               class="badge badge-outline"
                                               title="Download {{ $file->original_filename }}">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> {{ Str::limit($file->original_filename, 25) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                {!! nl2br($processedContent) !!}
                                @if ($isCurrentUser)
                                    <button class="btn btn-ghost btn-xs btn-circle absolute top-0 right-0 opacity-50 hover:opacity-100 delete-message-btn"
                                            title="Delete pair" data-message-id="{{ $message->id }}">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                @endif
                            </div>
                            @if ($isAssistant)
                                <div class="chat-footer opacity-50 flex items-center gap-1 mt-1">
                                    <button class="btn btn-ghost btn-xs copy-btn" title="Copy text"
                                            data-message-id="{{ $message->id }}"><i class="bi bi-clipboard"></i>
                                    </button>
                                    <button class="btn btn-ghost btn-xs read-aloud-btn" title="Read aloud"
                                            data-message-id="{{ $message->id }}">
                                        <i class="bi bi-play-circle"></i>
                                        <span class="loading loading-spinner loading-xs" style="display: none;"></span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        {{-- END: MODIFIED MESSAGE STRUCTURE --}}
                    @endforeach
                @endif
            </div>
            {{-- Loading Indicator --}}
            <div id="chat-loader" class="text-center p-2 hidden">
                <span class="loading loading-dots loading-md"></span>
            </div>
            {{-- Message Input Area (MODIFIED: Added padding to match chat history) --}}
            <div class="w-full pt-4 px-4 pb-4">
                <div id="typing-indicator" class="text-left px-2 pb-2 text-sm text-base-content/70 hidden"
                     style="min-height: 28px;">
                </div>
                <form id="chat-input-form" class="w-full max-w-4xl mx-auto"
                      data-participants='{{ json_encode($mentionableParticipants) }}'>
                    @csrf
                    <input type="hidden" id="team_id" name="team_id" value="{{ $team->id }}">
                    <input type="hidden" id="group_chat_header_id" name="group_chat_header_id"
                           value="{{ $activeChat?->id }}">
                    <input type="hidden" id="current_user_id" value="{{ auth()->id() }}">
                    <input type="hidden" id="attached-files-input" name="attached_files">
                    <div id="file-pills-container" class="flex flex-wrap gap-2 mb-2"></div>
                    <div class="form-control relative">
                        <div class="join w-full">
                            <button type="button" class="btn join-item" id="attach-file-btn"><i
                                        class="bi bi-paperclip text-xl"></i></button>
                            <textarea class="textarea textarea-bordered join-item w-full" id="message-input-field"
                                      name="message" placeholder="Message {{ $team->name }}... (use @ to mention)"
                                      rows="1" style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary join-item" id="send-message-button"
                                    title="Send">
                                <i class="bi bi-send-fill text-xl"></i>
                            </button>
                        </div>
                        <div id="mentions-dropdown"
                             class="absolute bottom-full mb-1 w-full max-w-xs bg-base-200 rounded-box shadow-lg z-10 hidden">
                            <ul id="mentions-list" class="menu p-2 max-h-36 overflow-y-auto" style="flex-wrap: inherit;">
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
        var groupParticipants = {!! json_encode($mentionableParticipants) !!};
    </script>
    <script src="{{ asset('js/group-chat.js') }}"></script>
@endpush
