<?php

namespace App\Services;

use App\Events\NewConversationEvent;
use App\Events\ConversationListUpdatedEvent;
use App\Models\Conversation;

class ConversationService
{
    public function createConversation(int $userId1, int $userId2): Conversation
    {
        $conversation = Conversation::firstOrCreate([
            'first_id' => min($userId1, $userId2),
            'second_id' => max($userId1, $userId2)
        ]);

        event(new NewConversationEvent($conversation));
        event(new ConversationListUpdatedEvent($userId1, $conversation));
        event(new ConversationListUpdatedEvent($userId2, $conversation));

        return $conversation->load(['firstUser', 'secondUser']);
    }

    public function getUserConversations(int $userId)
    {
        return Conversation::with(['lastMessage', 'firstUser', 'secondUser'])
            ->where('first_id', $userId)
            ->orWhere('second_id', $userId)
            ->get()
            ->map(function ($conv) use ($userId) {
                $conv->other_user = $conv->first_id === $userId 
                    ? $conv->secondUser 
                    : $conv->firstUser;
                return $conv;
            });
    }
}