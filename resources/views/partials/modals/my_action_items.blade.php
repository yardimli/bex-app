<dialog id="actionItemsModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box flex flex-col">
        <h3 class="font-bold text-lg">My Action Items</h3>

        <div class="py-4 flex-grow overflow-y-auto">
            @auth {{-- Show dynamic content only if logged in --}}
            <ul class="space-y-2" id="actionItemsList">
                {{-- Action items will be loaded here by JavaScript --}}
                <li>Loading action items...</li>
            </ul>
            @else {{-- Show placeholder if guest --}}
            <p class="text-base-content/70 text-center my-4">Please log in to manage your action items.</p>
            @endauth
        </div>

        @auth {{-- Show add input only if logged in --}}
        <div class="modal-action mt-2">
            <div class="join w-full">
                <input type="text" class="input input-bordered join-item w-full" placeholder="New action item..." id="newActionItemInput" aria-label="New action item">
                <button class="btn btn-primary join-item" type="button" id="addActionItemButton">
                    <i class="bi bi-plus-lg me-1"></i> Add
                </button>
            </div>
        </div>
        @endauth

        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
