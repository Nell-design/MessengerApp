<?php

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
}
