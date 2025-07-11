<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
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
        return new Channel('conversation.'.$this->conversationId);
    }

    public function broadcastAs()
    {
        return 'message.deleted';
    }
}