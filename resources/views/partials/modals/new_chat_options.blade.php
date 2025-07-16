<dialog id="newChatOptionsModal" class="modal">
    <div class="modal-box w-auto">
        <h3 class="font-bold text-lg">Start a New Chat</h3>
        <p class="py-4">Choose the type of chat you want to start.</p>
        <div class="flex justify-around gap-4">
            <a href="{{ route('chat.show') }}" class="btn btn-outline flex-2">
                <i class="bi bi-person-fill text-xl"></i>
                Personal Chat
            </a>
            <a href="#" id="start-group-chat-link" class="btn btn-outline flex-2">
                <i class="bi bi-people-fill text-xl"></i>
                Group Chat
            </a>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-sm btn-ghost">Cancel</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
