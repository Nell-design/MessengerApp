<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;

// Page d'accueil publique
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route d'authentification des broadcasts
Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware('web');

// Route de test pour vérifier le broadcasting
Route::get('/test-broadcast', function () {
    $message = \App\Models\Message::with('sender', 'conversation')->first();
    if ($message) {
        \Log::info('🧪 Test de broadcast avec message existant', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id
        ]);
        
        event(new \App\Events\MessageSentEvent($message));
        
        return response()->json([
            'success' => true,
            'message' => 'Événement MessageSentEvent diffusé',
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id
        ]);
    }
    
    return response()->json([
        'error' => 'Aucun message trouvé pour le test'
    ], 404);
});

// Page de messagerie après connexion
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard'); // messagerie Inertia
    })->name('dashboard');

    // Users
    Route::post('/users/status', [UserController::class, 'updateStatus']);
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/all', [UserController::class, 'listAll']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/start', [ConversationController::class, 'start']);
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);

    // Messages
    Route::post('/messages', [MessageController::class, 'store']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::post('/conversations/{conversation}/messages/read', [MessageController::class, 'markConversationAsRead']);
    Route::post('/messages/typing', [MessageController::class, 'typingStatus']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/conversation/{conversation}/read', [NotificationController::class, 'markConversationAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
});

// Authentification
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';