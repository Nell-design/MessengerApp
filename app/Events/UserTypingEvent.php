<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTypingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $userId;
    public $isTyping;
    public $receiverId;

    public function __construct($conversationId, $userId, $isTyping, $receiverId)
    {
       // dd($conversationId, $isTyping, $userId, $receiverId);
       $this->conversationId = $conversationId;
       $this->userId = $userId;
       $this->isTyping = $isTyping;
       $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        // Canal public séparé pour le typing
        return [
            new Channel('typingPublic.' . $this->conversationId . '.' . $this->isTyping)
        ];
    }

    /* public function broadcastAs()
    {
        return 'UserTypingEvent';
    } */

    public function broadcastWith()
    {
        return [
            
            'is_typing' => $this->isTyping,
            'conversation_id' => $this->conversationId,
            'receiver_id' => $this->receiverId
        ];
    }
}