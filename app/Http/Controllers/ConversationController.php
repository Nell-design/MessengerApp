<?php

namespace App\Http\Controllers;

use App\Events\NewConversationEvent;
use App\Http\Requests\CreateConversationRequest;
use App\Models\Conversation;
use App\Services\ConversationService;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    public function index()
    {
        $conversations = $this->conversationService->getUserConversations(auth()->id());
        return response()->json($conversations);
    }

    public function store(CreateConversationRequest $request)
    {
        $conversation = $this->conversationService->createConversation(
            auth()->id(),
            $request->input('user_id')
        );

        return response()->json($conversation, 201);
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->visibleToUser(auth()->id())
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(fn($msg) => $msg->created_at->format('Y-m-d'));

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
            'other_user' => $conversation->otherUser
        ]);
    }
}