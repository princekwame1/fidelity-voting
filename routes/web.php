<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Profile management routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::delete('/avatar', [App\Http\Controllers\ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        Route::get('/activity', [App\Http\Controllers\ProfileController::class, 'activity'])->name('profile.activity');
        Route::get('/preferences', [App\Http\Controllers\ProfileController::class, 'preferences'])->name('profile.preferences');
        Route::patch('/preferences', [App\Http\Controllers\ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Event management routes
    Route::resource('events', App\Http\Controllers\Admin\EventController::class, [
        'as' => 'admin'
    ]);

    // Event results and stats
    Route::get('events/{event}/results', [App\Http\Controllers\Admin\EventController::class, 'results'])
        ->name('admin.events.results');
    Route::get('events/{event}/stats', [App\Http\Controllers\Admin\EventController::class, 'stats'])
        ->name('admin.events.stats');

    // QR Code management routes
    Route::prefix('events/{event}/qrcodes')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\QRCodeController::class, 'index'])
            ->name('admin.events.qrcodes.index');
        Route::get('/download-sheet', [App\Http\Controllers\Admin\QRCodeController::class, 'downloadSheet'])
            ->name('admin.events.qrcodes.download-sheet');
        Route::get('/download', [App\Http\Controllers\Admin\QRCodeController::class, 'downloadSingle'])
            ->name('admin.events.qrcodes.download');
        Route::get('/download-png', [App\Http\Controllers\Admin\QRCodeController::class, 'downloadPng'])
            ->name('admin.events.qrcodes.download-png');
        Route::get('/preview', [App\Http\Controllers\Admin\QRCodeController::class, 'preview'])
            ->name('admin.events.qrcodes.preview');
    });

    // Security management routes
    Route::prefix('security')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SecurityController::class, 'dashboard'])
            ->name('admin.security.dashboard');
        Route::get('/event/{event}', [App\Http\Controllers\Admin\SecurityController::class, 'eventSecurity'])
            ->name('admin.security.event');
        Route::get('/suspicious', [App\Http\Controllers\Admin\SecurityController::class, 'suspiciousActivity'])
            ->name('admin.security.suspicious');
        Route::post('/block-ip', [App\Http\Controllers\Admin\SecurityController::class, 'blockIp'])
            ->name('admin.security.block-ip');
        Route::post('/unblock-ip', [App\Http\Controllers\Admin\SecurityController::class, 'unblockIp'])
            ->name('admin.security.unblock-ip');
        Route::post('/revoke-session/{session}', [App\Http\Controllers\Admin\SecurityController::class, 'revokeSession'])
            ->name('admin.security.revoke-session');
        Route::get('/data', [App\Http\Controllers\Admin\SecurityController::class, 'getSecurityData'])
            ->name('admin.security.data');
    });
});

// Public voting routes (no authentication required)
Route::prefix('vote')->group(function () {
    Route::get('/event/{event}', [App\Http\Controllers\VoteController::class, 'show'])
        ->name('vote.show');
    Route::post('/event/{event}/email', [App\Http\Controllers\VoteController::class, 'submitEmail'])
        ->name('vote.email');
    Route::post('/event/{event}', [App\Http\Controllers\VoteController::class, 'submit'])
        ->name('vote.submit');
    Route::get('/success', [App\Http\Controllers\VoteController::class, 'success'])
        ->name('vote.success');
});

// Public results with real-time updates
Route::get('results/{event}', [App\Http\Controllers\VoteController::class, 'results'])
    ->name('vote.results');
Route::get('results/{event}/data', [App\Http\Controllers\VoteController::class, 'resultsData'])
    ->name('vote.results.data');

// Event QR code display (single QR per event)
Route::get('event/{event}/qr', [App\Http\Controllers\Admin\EventController::class, 'displayQR'])
    ->name('event.qr');
