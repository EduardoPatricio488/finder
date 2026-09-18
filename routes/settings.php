<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'conta');
    Route::redirect('settings/profile', 'conta');
    Route::redirect('settings/appearance', 'conta#aparencia');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings/security', 'conta#seguranca');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('account'),
        'manage' => route('account'),
    ]);
})->name('well-known.passkeys');
