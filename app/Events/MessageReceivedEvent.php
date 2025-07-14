<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class MessageReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverId;
    public $conversationId;

    public function __construct($message)
    {
        $this->message = $message->load('sender');
        $this->receiverId = $message->receiver_id;
        $this->conversationId = $message->conversation_id;
    }

    public function broadcastOn()
    {
        $channels = [
            new Channel('conversation.'.$this->conversationId),
            new Channel('user.'.$this->receiverId),
        ];
        
        \Log::info('✅ MessageReceivedEvent: Broadcast sur les canaux publics', [
            'conversation_id' => $this->conversationId,
            'receiver_id' => $this->receiverId,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'channels' => ['conversation.'.$this->conversationId, 'user.'.$this->receiverId]
        ]);
        
        return $channels;
    }

    public function broadcastAs()
    {
        return 'MessageReceivedEvent';
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversationId,
            'receiver_id' => $this->receiverId,
            'sender_id' => $this->message->sender_id,
            'is_read' => $this->message->is_read,
        ];
    }
}