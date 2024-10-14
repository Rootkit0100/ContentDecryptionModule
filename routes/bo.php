<?php

use ContentDecryptionPlugin\Http\Controllers\ContentDecryptionController;

// web, auth, check.permission are default for BO access
Route::middleware('web')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::middleware('check.permission')->group(function () {
            Route::get('/content_decryption/index', [ContentDecryptionController::class, 'index'])->name('content_decryption.index');
            Route::get('/content_decryption/create', [ContentDecryptionController::class, 'create'])->name('content_decryption.create');
            Route::get('/content_decryption/{stream_mpd}/edit', [ContentDecryptionController::class, 'edit'])->name('content_decryption.edit');
            Route::put('/content_decryption/{stream_mpd}', [ContentDecryptionController::class, 'update'])->name('content_decryption.update');
            Route::post('/content_decryption/store', [ContentDecryptionController::class, 'store'])->name('content_decryption.store');
            Route::get('/content_decryption/data', [ContentDecryptionController::class, 'data'])->name('content_decryption.data');
            Route::post('/content_decryption/{stream_mpd}/start', [ContentDecryptionController::class, 'start'])->name('content_decryption.start');
            Route::post('/content_decryption/{stream_mpd}/stop', [ContentDecryptionController::class, 'stop'])->name('content_decryption.stop');
            Route::delete('/content_decryption/{stream_mpd}/delete', [ContentDecryptionController::class, 'destroy'])->name('content_decryption.destroy');
        });
    });
});
