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

class NewConversationEvent implements ShouldBroadcastNow
{
    public $conversation;

    public function __construct($conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return [
            new Channel('user.'.$this->conversation->first_id),
            new Channel('user.'.$this->conversation->second_id)
        ];
    }
}
