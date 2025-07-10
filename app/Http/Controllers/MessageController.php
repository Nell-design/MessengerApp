<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;
use App\Models\Message;

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

    public function destroy(Message $message)
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
        return response()->noContent();
    }
}