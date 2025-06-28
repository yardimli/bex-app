<div class="modal fade" id="teamFilesModal" tabindex="-1" aria-labelledby="teamFilesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamFilesModalLabel">Team Workspace</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 team-files-sidebar">
                        <div class="text-center mb-4">
                            <i class="bi bi-people-fill" style="font-size: 3rem; color: #0d6efd;"></i>
                            <h5 id="team-files-modal-team-name">Your Team</h5>
                        </div>
                        <div class="list-group list-group-flush" id="team-files-modal-filters">
                            {{-- "Recent" is the default and is functional. --}}
                            <a href="#" class="list-group-item list-group-item-action" data-filter="all">All Files</a>
                            <a href="#" class="list-group-item list-group-item-action active" data-filter="recent">Recent</a>
                            <a href="#" class="list-group-item list-group-item-action disabled" title="Coming soon">Shared with me</a>
                            <a href="#" class="list-group-item list-group-item-action disabled" title="Coming soon">Favorites</a>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search files..." id="team-files-modal-search">
                        </div>
                        {{-- The list of files will be dynamically inserted here --}}
                        <div class="team-files-list" id="team-files-modal-list">
                            {{-- Placeholder content while loading --}}
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        {{-- The details pane for when a file is clicked --}}
                        <div class="details-pane mt-3 border rounded p-3" id="team-files-modal-details-pane" style="min-height: 150px;">
                            Select a file to view details.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
