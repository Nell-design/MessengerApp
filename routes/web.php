<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ContactController;

// Page d’accueil publique
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Page de messagerie après connexion
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard'); // messagerie Inertia
    })->name('dashboard');

    // Users
    Route::post('/users/status', [UserController::class, 'updateStatus']);
    Route::get('/users/search', [UserController::class, 'search']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);

    // Messages
    Route::post('/messages', [MessageController::class, 'store']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages/typing', [MessageController::class, 'typingStatus']);

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
});

// Authentification
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';