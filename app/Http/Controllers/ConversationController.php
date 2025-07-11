<?php

namespace App\Http\Controllers;

use App\Events\NewConversationEvent;
use App\Http\Requests\CreateConversationRequest;
use App\Models\Conversation;
use App\Services\ConversationService;
use Inertia\Inertia;

class ConversationController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private ConversationService $conversationService
    ) {}


    public function index()
{
    $conversations = $this->conversationService->getUserConversations(auth()->id());

    // Formate les données à envoyer à Vue
    $formatted = collect($conversations)->map(function ($conv) {
        return [
            'id' => $conv->id,
            'name' => $conv->other_user->name,
            'message' => optional($conv->last_message)->contenu ?? '',
            'time' => optional($conv->last_message)->created_at?->format('H:i') ?? '',
        ];
    });

    return Inertia::render('Messagerie/Index', [
        'conversations' => $formatted,
    ]);
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