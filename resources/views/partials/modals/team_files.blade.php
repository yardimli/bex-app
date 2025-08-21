{{-- Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="teamFilesModal" class="modal">
    <div class="modal-box w-11/12 max-w-6xl">
        <h3 class="font-bold text-lg">Workspace</h3>
        <div class="py-4">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="md:w-1/4">
                    <div class="text-center mb-4">
                        <div class="avatar">
                            <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                <img id="team-files-modal-avatar" src="https://ui-avatars.com/api/?name=Team" alt="Workspace Avatar" />
                            </div>
                        </div>
                        <h4 class="font-semibold mt-2" id="team-files-modal-team-name">Your Team</h4>
                    </div>
                    <ul class="menu bg-base-200 rounded-box" id="team-files-modal-filters">
                        <li><a data-filter="all">All Files</a></li>
                        <li><a class="active" data-filter="recent">Recent</a></li>
                        <li><a class="disabled" title="Coming soon">Shared with me</a></li>
                        <li><a data-filter="favorites">Favorites</a></li>
                    </ul>
                </div>
                <div class="md:w-3/4">
                    <div class="form-control w-full mb-3">
                        <label class="input input-bordered flex items-center gap-2">
                            <i class="bi bi-search"></i>
                            <input type="text" class="grow" placeholder="Search files..." id="team-files-modal-search">
                        </label>
                    </div>
                    <div class="space-y-2 h-96 overflow-y-auto p-1" id="team-files-modal-list">
                        {{-- Added a dedicated loader for the file list --}}
                        <div class="flex justify-center items-center h-full" id="team-files-modal-loader">
                            <span class="loading loading-spinner loading-lg"></span>
                        </div>
                    </div>
                    <div class="mt-3 border border-base-300 rounded-box p-4 bg-base-200 min-h-32" id="team-files-modal-details-pane">
                        Select a file to view details.
                    </div>
                </div>
            </div>
        </div>
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
