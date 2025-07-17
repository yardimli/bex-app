<dialog id="groupChatSetupModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">New Group Chat</h3>
        <form id="groupChatSetupForm" class="py-4 space-y-4">
            <div>
                <label for="group-chat-title" class="label"><span class="label-text">Chat Name</span></label>
                <input type="text" id="group-chat-title" placeholder="e.g., Q3 Project Planning" class="input input-bordered w-full" required />
            </div>
            <div>
                <label class="label"><span class="label-text">Participants</span></label>
                <div id="group-chat-participant-list" class="max-h-60 overflow-y-auto p-2 border border-base-300 rounded-box space-y-2">
                    {{-- Participant checkboxes will be loaded here by JS --}}
                    <div class="text-center"><span class="loading loading-spinner"></span></div>
                </div>
            </div>
        </form>
        <div class="modal-action">
            <button type="button" class="btn btn-primary" id="create-group-chat-btn">Create Chat</button>
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
