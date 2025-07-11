<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageReceivedEvent extends MessageSentEvent implements ShouldBroadcastNow
{
    public function broadcastOn()
    {
        return new PrivateChannel('user.'.$this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'message.received';
    }
}