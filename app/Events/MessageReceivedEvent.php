<?php 


namespace App\Events;

use Illuminate\Broadcasting\Channel;

class MessageReceivedEvent extends MessageSentEvent
{
    /**
     * Le nom de l'événement.
     *
     * @var string
     */
    public $name = 'message.received';

    public function __construct($message)
    {
        parent::__construct($message);
    }

    public function broadcastOn()
    {
        return new Channel('user.'.$this->message->receiver_id);
    }

    public function broadcastAs()
    {
        return 'message.received';
    }
}