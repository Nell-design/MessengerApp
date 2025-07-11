<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return \App\Models\Conversation::where('id', $conversationId)
        ->where(function($q) use ($user) {
            $q->where('first_id', $user->id)
            ->orWhere('second_id', $user->id);
        })->exists();
});

Broadcast::channel('user.{userId}', function ($authUser, $userId) {
    return (int)$authUser->id === (int)$userId;
});
