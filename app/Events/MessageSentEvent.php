<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSentEvent implements ShouldBroadcastNow
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn()
    {
        return new Channel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
