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

class MessageNotificationEvent implements ShouldBroadcastNow
{
    public $userId;
    public $notification;

    public function __construct($userId, $notificationData)
    {
        $this->userId = $userId;
        $this->notification = $notificationData;
    }

    public function broadcastOn()
    {
        return new Channel('user.'.$this->userId);
    }
}