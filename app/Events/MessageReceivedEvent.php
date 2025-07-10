<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;

class MessageReceivedEvent extends MessageSentEvent
{
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'message.received';
    }
}