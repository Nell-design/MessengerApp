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

    public function __construct($conversationId, $userId, $isTyping)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        \Log::info('⌨️ UserTypingEvent: Broadcast sur le canal public', [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'is_typing' => $this->isTyping,
            'channel' => 'conversation.'.$this->conversationId
        ]);
        
        return new Channel('conversation.'.$this->conversationId);
    }

    public function broadcastAs()
    {
        return 'UserTypingEvent';
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'is_typing' => $this->isTyping
        ];
    }
}