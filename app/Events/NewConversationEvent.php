<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewConversationEvent implements ShouldBroadcastNow
{
    public $conversation;

    public function __construct($conversation)
    {
        $this->conversation = $conversation->load(['firstUser', 'secondUser']);
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.'.$this->conversation->first_id),
            new PrivateChannel('user.'.$this->conversation->second_id)
        ];
    }

    public function broadcastWith()
    {
        return [

            'conversation' => [
                'id' => $this->conversation->id,
                'users' => [
                    $this->conversation->firstUser,
                    $this->conversation->secondUser

                ]
            ]
        ];
    }
}
