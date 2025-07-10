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

namespace App\Events;

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
        return new Channel('user.'.$this->userId);
    }
}