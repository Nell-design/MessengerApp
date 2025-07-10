<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

namespace App\Events;

class MessageDeletedForEveryoneEvent extends MessageDeletedEvent
{
    public function broadcastWith()
    {
        return [
            'action' => 'delete_for_everyone',
            'message_id' => $this->messageId,
            'conversation_id' => $this->conversationId
        ];
    }

    public function broadcastAs()
    {
        return 'message.deleted_for_everyone';
    }
}