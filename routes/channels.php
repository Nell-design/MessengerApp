<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $hasAccess = \App\Models\Conversation::where('id', $conversationId)
        ->where(function($q) use ($user) {
            $q->where('first_id', $user->id)
            ->orWhere('second_id', $user->id);
        })->exists();
    
    \Log::info('🔐 Autorisation canal conversation', [
        'user_id' => $user->id,
        'conversation_id' => $conversationId,
        'has_access' => $hasAccess
    ]);
    
    return $hasAccess;
});

Broadcast::channel('user.{userId}', function ($authUser, $userId) {
    $hasAccess = (int)$authUser->id === (int)$userId;
    
    \Log::info('🔐 Autorisation canal utilisateur', [
        'auth_user_id' => $authUser->id,
        'requested_user_id' => $userId,
        'has_access' => $hasAccess
    ]);
    
    return $hasAccess;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    $hasAccess = (int) $user->id === (int) $id;
    
    \Log::info('🔐 Autorisation canal modèle utilisateur', [
        'user_id' => $user->id,
        'requested_id' => $id,
        'has_access' => $hasAccess
    ]);
    
    return $hasAccess;
});
