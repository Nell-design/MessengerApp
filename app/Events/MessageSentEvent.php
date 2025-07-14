<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverId;
    public $conversationId;

    public function __construct($message)
    {
        // dd($message);
        \Log::info('MessageSentEvent: ', ['message' => $message]);
        $this->message = $message->load('sender');
        $this->receiverId = $message->receiver_id;
        $this->conversationId = $message->conversation_id;
    }

    public function broadcastOn()
    {
        // dd('Je suis dans le broadCastOn de bhraYane');
        $channels = [
            new Channel('conversation'),
            // new Channel('user.'.$this->receiverId), // Pour les notifications push
        ];
        
        // \Log::info('📨 MessageSentEvent: Broadcast sur les canaux publics', [
        //     'conversation_id' => $this->conversationId,
        //     'receiver_id' => $this->receiverId,
        //     'sender_id' => $this->message->sender_id,
        //     'channels' => ['conversation.'.$this->conversationId, 'user.'.$this->receiverId],
        //     'message_id' => $this->message->id,
        //     'broadcast_connection' => config('broadcasting.default'),
        //     'message_content' => $this->message->content
        // ]);
        
        return $channels;
    }

    // public function broadcastAs()
    // {
    //     return 'MessageSentEvent';
    // }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'receiver_id' => $this->receiverId,
            'conversation_id' => $this->conversationId,
            'sender_name' => $this->message->sender->name,
            'conversation_name' => $this->message->conversation->name ?? 'Nouveau message',
        ];
    }
}