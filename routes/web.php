<?php

use App\Http\Controllers\WalletPassController;
use App\Models\Member;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard', [
            'totalMembers' => Member::where('status', 'active')->count(),
            'cardsSent' => Member::whereNotNull('email_sent_at')->count(),
            'passesDownloaded' => Member::where(function ($query) {
                $query->whereNotNull('apple_wallet_downloaded_at')
                    ->orWhereNotNull('google_wallet_downloaded_at');
            })->count(),
        ]);
    })->name('dashboard');
    Route::livewire('members', 'pages::members.index')->name('members.index');
});

Route::get('passes/apple/{uuid}', [WalletPassController::class, 'applePass'])->name('members.pass.apple');
Route::get('passes/google/{uuid}', [WalletPassController::class, 'googlePass'])->name('members.pass.google');

require __DIR__.'/settings.php';
