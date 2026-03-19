<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TransactionDetailController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


Route::get('/clear-config', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return "Config cleared";
});


// Route::get('/server-path', function () {
//     return base_path();
// });

// Route::get('/run-draw', function () {
//     Artisan::call('draw:generate-results');
//     return "Draw command executed";
// });

// 🔹 When user opens "/", show login screen
Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('home');

// 🔹 Dashboard (after login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 🔹 USER MANAGEMENT ROUTES
//     (No middleware restrictions here – we will restrict via view logic)
Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {

    Route::get('/',           [UserController::class, 'index'])->name('index');
    Route::get('/create',     [UserController::class, 'create'])->name('create');
    Route::post('/store',     [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit',  [UserController::class, 'edit'])->name('edit');
    Route::put('/{id}',       [UserController::class, 'update'])->name('update');
    Route::delete('/{id}',    [UserController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/balance', [UserController::class, 'balanceUpdate'])->name('balance.update');
    Route::get('/{id}/oversight', [UserController::class, 'oversight'])->name('oversight');
});

// Lottory Menu Pages
Route::middleware(['auth'])->group(function () {
    Route::get('/accounts',      [AccountController::class, 'accounts'])->name('account.index');
    Route::get('/transaction-details', [TransactionDetailController::class, 'index'])->name('transactions.index');
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/reprint',       [LotteryController::class, 'reprint'])->name('lotto.reprint');
    Route::get('/claim',         [ClaimController::class, 'claim'])->name('claim.index');
});



// 🔹 Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/place-bet', [BetController::class, 'placeBet'])->name('bet.place');
});


// 🔹 Auth routes (login / register / forgot password)
require __DIR__ . '/auth.php';
