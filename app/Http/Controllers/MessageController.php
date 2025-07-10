<?php

namespace App\Http\Controllers;

use App\Events\MessageSentEvent;
use App\Events\MessageReadEvent;
use App\Events\MessageDeletedEvent;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    public function store(StoreMessageRequest $request)
    {
        $message = $this->messageService->sendMessage($request->validated());
        return response()->json($message, 201);
    }

    public function destroy(Message $message, Request $request)
    {
        $this->authorize('delete', $message);
        
        $result = $this->messageService->deleteMessage(
            $message,
            $request->input('for_everyone', false)
        );

        return response()->json([
            'status' => $result === 'permanent' ? 'permanently_deleted' : 'soft_deleted'
        ]);
    }

    public function markAsRead(Message $message)
    {
        $this->authorize('view', $message);
        $message->markAsRead();
        
        broadcast(new MessageReadEvent($message))->toOthers();
        
        return response()->noContent();
    }

    public function typingStatus(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'is_typing' => 'required|boolean'
        ]);

        broadcast(new UserTypingEvent(
            $request->conversation_id,
            auth()->id(),
            $request->is_typing
        ))->toOthers();

        return response()->noContent();
    }
}