<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class UserTypingEvent implements ShouldBroadcastNow
{
    public $conversationId;
    public $userId;
    public $isTyping;

    public function __construct($conversationId, $userId, $isTyping)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return new Channel('conversation.'.$this->conversationId);
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'typing' => $this->isTyping
        ];
    }
}