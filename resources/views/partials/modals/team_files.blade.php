{{-- MODIFIED: Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="teamFilesModal" class="modal">
    <div class="modal-box w-11/12 max-w-6xl">
        <h3 class="font-bold text-lg">Team Workspace</h3>
        
        <div class="py-4">
            {{-- MODIFIED: Replaced row/col with flexbox --}}
            <div class="flex flex-col md:flex-row gap-6">
                {{-- MODIFIED: Sidebar column --}}
                <div class="md:w-1/4">
                    <div class="text-center mb-4">
                        <i class="bi bi-people-fill text-5xl text-primary"></i>
                        <h4 class="font-semibold mt-2" id="team-files-modal-team-name">Your Team</h4>
                    </div>
                    {{-- MODIFIED: Replaced list-group with DaisyUI menu --}}
                    <ul class="menu bg-base-200 rounded-box" id="team-files-modal-filters">
                        <li><a data-filter="all">All Files</a></li>
                        <li><a class="active" data-filter="recent">Recent</a></li>
                        <li><a class="disabled" title="Coming soon">Shared with me</a></li>
                        <li><a class="disabled" title="Coming soon">Favorites</a></li>
                    </ul>
                </div>
                {{-- MODIFIED: Main content column --}}
                <div class="md:w-3/4">
                    <div class="form-control w-full mb-3">
                        <label class="input input-bordered flex items-center gap-2">
                            <i class="bi bi-search"></i>
                            <input type="text" class="grow" placeholder="Search files..." id="team-files-modal-search">
                        </label>
                    </div>
                    {{-- MODIFIED: File list container --}}
                    <div class="space-y-2 h-96 overflow-y-auto p-1" id="team-files-modal-list">
                        {{-- Placeholder content while loading --}}
                        <div class="flex justify-center items-center h-full">
                            <span class="loading loading-spinner loading-lg"></span>
                        </div>
                    </div>
                    {{-- MODIFIED: Details pane --}}
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
