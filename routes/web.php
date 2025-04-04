<?php

	use Illuminate\Support\Facades\Route;
	use App\Http\Controllers\AppController;

	Route::get('/', [AppController::class, 'index'])->name('dashboard');

	// --- Placeholder AJAX Routes (Uncomment and implement controller methods later) ---
	// Route::prefix('api')->group(function () {
	//     Route::get('/meetings/{meetingId}', [AppController::class, 'getMeetingDetails']);
	//     Route::get('/notes/{noteId}', [AppController::class, 'getNoteDetails']);
	//     Route::get('/files/{fileId}', [AppController::class, 'getFileDetails']);
	//     Route::get('/recordings/{recordingId}', [AppController::class, 'getRecordingDetails']);
	//     Route::get('/action-items', [AppController::class, 'getActionItems']);
	//     Route::post('/action-items', [AppController::class, 'addActionItem']);
	//     Route::post('/settings', [AppController::class, 'updateSettings']);
	//     Route::post('/summarize', [AppController::class, 'summarize']);
	//     Route::post('/transcribe', [AppController::class, 'transcribe']);
	// });
