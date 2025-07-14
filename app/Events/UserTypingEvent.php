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
        $conversation = \App\Models\Conversation::find($this->conversationId);
        $channels = [
            new Channel('conversation.' . $this->conversationId),
        ];
        if ($conversation) {
            $channels[] = new Channel('user.' . $conversation->first_id);
            $channels[] = new Channel('user.' . $conversation->second_id);
        }
        \Log::info('⌨️ UserTypingEvent: Broadcast sur les canaux', [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'is_typing' => $this->isTyping,
            'channels' => array_map(function($c) { return method_exists($c, 'name') ? $c->name() : (string)$c; }, $channels)
        ]);
        return $channels;
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