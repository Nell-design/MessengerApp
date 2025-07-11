<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ConversationListUpdatedEvent implements ShouldBroadcastNow
{
    public $userId;
    public $conversation;

    public function __construct($userId, $conversation)
    {
        $this->userId = $userId;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.'.$this->userId); // Changé en PrivateChannel
    }

    public function broadcastAs()
    {
        return 'conversation.updated';
    }
}