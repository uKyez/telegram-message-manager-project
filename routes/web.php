<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\TelegramUserController;
use App\Http\Controllers\TelegramGroupController;
use App\Http\Controllers\MessageLogController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Webhook do Telegram (sem middleware de autenticação)
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rotas do painel administrativo
    Route::resource('scheduled-messages', ScheduledMessageController::class);
    Route::resource('telegram-users', TelegramUserController::class)->except(['create', 'store']);
    Route::resource('telegram-groups', TelegramGroupController::class)->except(['create', 'store']);
    Route::resource('message-logs', MessageLogController::class)->only(['index', 'show']);
});

require __DIR__.'/auth.php';
