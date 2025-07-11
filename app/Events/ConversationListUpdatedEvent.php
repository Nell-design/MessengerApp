<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class ConversationListUpdatedEvent implements ShouldBroadcastNow
{
    public $userId;
    public $conversation;
    public $action; // 'created', 'updated', 'deleted'

    public function __construct($userId, $conversation, $action = 'created')
    {
        $this->userId = $userId;
        $this->conversation = $conversation->load(['firstUser', 'secondUser', 'lastMessage']);
        $this->action = $action;
    }

    public function broadcastOn()
    {
        Log::info('🔄 ConversationListUpdatedEvent: Broadcast sur le canal public', [
            'user_id' => $this->userId,
            'conversation_id' => $this->conversation->id,
            'action' => $this->action,
            'channel' => 'user.'.$this->userId,
            'broadcast_connection' => config('broadcasting.default'),
            'conversation_name' => $this->conversation->name ?? 'N/A'
        ]);
        
        return new Channel('user.'.$this->userId);
    }

    public function broadcastAs()
    {
        return 'conversation.updated';
    }

    public function broadcastWith()
    {
        // Déterminer l'autre utilisateur
        $otherUser = $this->conversation->first_id === $this->userId 
            ? $this->conversation->secondUser 
            : $this->conversation->firstUser;
        
        // Compter les messages non lus
        $unreadCount = $this->conversation->messages()
            ->where('receiver_id', $this->userId)
            ->where('is_read', false)
            ->where('deleted_for_receiver', false)
            ->count();
        
        $data = [
            'action' => $this->action,
            'conversation' => [
                'id' => $this->conversation->id,
                'name' => $otherUser?->name ?? 'Utilisateur inconnu',
                'avatar' => $otherUser?->avatar ?? '/default-avatar.png',
                'unread_count' => $unreadCount,
                'last_message' => $this->conversation->lastMessage ? [
                    'content' => $this->conversation->lastMessage->content,
                    'sender_id' => $this->conversation->lastMessage->sender_id,
                    'sender_name' => $this->conversation->lastMessage->sender?->name ?? '',
                    'created_at' => $this->conversation->lastMessage->created_at,
                    'is_read' => $this->conversation->lastMessage->is_read,
                ] : null,
                'time' => $this->conversation->lastMessage?->created_at?->diffForHumans() ?? '',
            ]
        ];
        
        Log::info('📤 ConversationListUpdatedEvent: Données diffusées', [
            'user_id' => $this->userId,
            'conversation_id' => $this->conversation->id,
            'action' => $this->action,
            'data' => $data
        ]);
        
        return $data;
    }
}