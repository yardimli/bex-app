{{-- Converted from Bootstrap modal to DaisyUI dialog --}}
<dialog id="transcribeModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl">
        <h3 class="font-bold text-lg">Transcribe Audio/Video</h3>

        <div class="py-4">
            {{-- Hidden file input --}}
            <input type="file" id="transcribe-file-input" class="hidden" accept="audio/*,video/*,.mp3,.mp4,.mpeg,.mpga,.m4a,.wav,.webm">

            {{-- Replaced row/col with a responsive grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Replaced card with DaisyUI card --}}
                <div class="card bg-base-200 shadow-md text-center">
                    <div class="card-body items-center">
                        <i class="bi bi-upload text-4xl text-primary"></i>
                        <h4 class="card-title">Open Files...</h4>
                        <p class="text-sm text-base-content/70">Upload audio or video files</p>
                        <div class="card-actions justify-center mt-2">
                            <button class="btn btn-primary" id="transcribe-upload-btn">Upload</button>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-200 shadow-md text-center">
                    <div class="card-body items-center">
                        <i class="bi bi-mic-fill text-4xl text-error"></i>
                        <h4 class="card-title">New Recording</h4>
                        <p class="text-sm text-base-content/70">Start a new voice recording</p>
                        <div class="card-actions justify-center mt-2">
                            <button class="btn btn-error" id="transcribe-record-btn">Record</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mt-6">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text">Input Language (Optional)</span></label>
                    <select class="select select-bordered" id="transcribeInputLanguage">
                        <option value="en" selected>English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="it">Italian</option>
                        <option value="ja">Japanese</option>
                        <option value="ko">Korean</option>
                        <option value="pt">Portuguese</option>
                        <option value="ru">Russian</option>
                        <option value="zh">Chinese</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Leave as English for auto-detection.</span>
                    </label>
                </div>
                <div class="form-control w-full">
                    <label class="label"><span class="label-text">Quality</span></label>
                    <select class="select select-bordered" id="transcribeQuality" disabled>
                        <option selected>Whisper Large V3</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Model is configured by the administrator.</span>
                    </label>
                </div>
            </div>
        </div>

        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
