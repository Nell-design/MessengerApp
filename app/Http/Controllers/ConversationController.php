<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateConversationRequest;
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
}