<?php

namespace App\Services;

use App\Events\MessageSentEvent;
use App\Events\MessageDeletedEvent;
use App\Events\MessageDeletedForEveryoneEvent;
use App\Models\Message;

class MessageService
{
    public function sendMessage(array $data): Message
    {
        $message = Message::create([
            'conversation_id' => $data['conversation_id'],
            'sender_id' => auth()->id(),
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content']
        ]);

        event(new MessageSentEvent($message));

        return $message->load('sender');
    }

    public function deleteMessage(Message $message, bool $forEveryone = false): string
    {
        if ($forEveryone) {
            $message->delete();
            event(new MessageDeletedForEveryoneEvent(
                $message->conversation_id,
                $message->id
            ));
            return 'deleted_for_everyone';
        }

        $result = $message->deleteForUser(auth()->id());
        event(new MessageDeletedEvent(
            $message->conversation_id,
            $message->id,
            auth()->id()
        ));

        return $result;
    }
}