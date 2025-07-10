<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ContactController;

Route::middleware(['auth:sanctum'])->group(function () {
    // User
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
