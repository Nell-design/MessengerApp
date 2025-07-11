<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageDeletedEvent implements ShouldBroadcastNow
{
    public $conversationId;
    public $messageId;
    public $deletedBy;

    public function __construct($conversationId, $messageId, $userId)
    {
        $this->conversationId = $conversationId;
        $this->messageId = $messageId;
        $this->deletedBy = $userId;
    }

    public function broadcastOn()
    {
        \Log::info('🗑️ MessageDeletedEvent: Broadcast sur le canal public', [
            'conversation_id' => $this->conversationId,
            'message_id' => $this->messageId,
            'deleted_by' => $this->deletedBy,
            'channel' => 'conversation.'.$this->conversationId
        ]);
        
        return new Channel('conversation.'.$this->conversationId);
    }

    public function broadcastAs()
    {
        return 'MessageDeletedEvent';
    }
}