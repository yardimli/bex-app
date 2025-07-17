@extends('layouts.app')

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
                        @endphp
                        <div class="chat {{ $alignment }}" id="message-{{ $message->id }}" data-message-content="{!! e($message->content) !!}">
                            <div class="chat-bubble {{ $bubbleColor }} relative">
                                {!! nl2br(e($message->content)) !!}
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
                    <div class="form-control">
                        <div class="join w-full">
                            <textarea class="textarea textarea-bordered join-item w-full" id="message-input-field" name="message" placeholder="Message {{ $team->name }}..." rows="1" style="resize: none;" required></textarea>
                            <button type="submit" class="btn btn-primary join-item" id="send-message-button" title="Send">
                                <i class="bi bi-send-fill text-xl"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/group-chat.js') }}"></script>
@endpush
