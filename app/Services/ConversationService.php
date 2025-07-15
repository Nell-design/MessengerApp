<?php

namespace App\Services;

use App\Events\ConversationListUpdatedEvent;
use App\Models\Conversation;

class ConversationService
{
    public function createConversation(int $userId1, int $userId2): Conversation
    {
        \Log::info('🆕 ConversationService: Création de conversation', [
            'user_id_1' => $userId1,
            'user_id_2' => $userId2
        ]);
        
        $conversation = Conversation::firstOrCreate([
            'first_id' => min($userId1, $userId2),
            'second_id' => max($userId1, $userId2)
        ]);

        \Log::info('✅ ConversationService: Conversation créée/trouvée', [
            'conversation_id' => $conversation->id,
            'first_id' => $conversation->first_id,
            'second_id' => $conversation->second_id
        ]);

        // Notifier les deux utilisateurs de la nouvelle conversation
        event(new ConversationListUpdatedEvent($userId1, $conversation, 'created'));
        event(new ConversationListUpdatedEvent($userId2, $conversation, 'created'));

        return $conversation->load(['firstUser', 'secondUser']);
    }

    public function getUserConversations(int $userId)
    {
        return Conversation::with(['lastMessage', 'firstUser', 'secondUser'])
            ->where('first_id', $userId)
            ->orWhere('second_id', $userId)
            ->get()
            ->map(function ($conv) use ($userId) {
                $otherUser = $conv->first_id === $userId 
                    ? $conv->secondUser 
                    : $conv->firstUser;
                $lastMsg = $conv->last_message; // Utilise l'accessor getLastMessageAttribute()
                
                // Compter les messages non lus reçus par l'utilisateur
                $unreadCount = $conv->messages()
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->where('deleted_for_receiver', false)
                    ->count();
                
                // Log temporaire pour debug
                \Log::info('Conversation debug', [
                    'conversation_id' => $conv->id,
                    'last_message_id' => $lastMsg?->id,
                    'last_message_content' => $lastMsg?->content,
                    'last_message_sender_id' => $lastMsg?->sender_id,
                    'last_message_created_at' => $lastMsg?->created_at,
                    'unread_count' => $unreadCount,
                ]);
                return [
                    'id' => $conv->id,
                    'first_id' => $conv->first_id,
                    'second_id' => $conv->second_id,
                    'name' => $otherUser?->name ?? 'Utilisateur inconnu',
                    'avatar' => $otherUser?->avatar, // null si absent
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMsg ? [
                        'content' => $lastMsg->content,
                        'sender_id' => $lastMsg->sender_id,
                        'sender_name' => $lastMsg->sender?->name ?? '',
                        'created_at' => $lastMsg->created_at,
                        'is_read' => $lastMsg->is_read,
                    ] : null,
                    'time' => $lastMsg?->created_at?->diffForHumans() ?? '',
                ];
            });
    }
}