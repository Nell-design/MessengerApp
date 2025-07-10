<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
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
}