<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\ConversationService;
use App\Http\Requests\CreateConversationRequest;

class ConversationController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private ConversationService $conversationService
    ) {}


    public function index()
    {
        $conversations = $this->conversationService->getUserConversations(auth()->id());
        return response()->json($conversations);
    }

    public function start(Request $request)
{
    try {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Log temporaire pour debugger
        \Log::info('Création conversation avec', ['from' => auth()->id(), 'to' => $request->user_id]);

        // Exemple simple de logique (à adapter selon ton modèle)
        $conversation = Conversation::firstOrCreate([
            'first_id' => min(auth()->id(), $request->user_id),
            'second_id' => max(auth()->id(), $request->user_id),
        ]);

        return response()->json([
            'id' => $conversation->id,
            'name' => $conversation->otherUser->name,
            'avatar' => $conversation->otherUser->avatar,
            'message' => '',
            'time' => now()->format('H:i'),
        ]);

    } catch (\Throwable $e) {
        \Log::error('Erreur création conversation : ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Erreur serveur : ' . $e->getMessage()
        ], 500);
    }
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
    $authId = auth()->id();

    // Sécurité : ne pas autoriser un utilisateur non concerné
    if ($authId !== $conversation->first_id && $authId !== $conversation->second_id) {
        abort(403);
    }

    return Inertia::render('ChatWindow', [
        'conversation' => [
            'id' => $conversation->id,
            'name' => $conversation->otherUser->name,
            'avatar' => $conversation->otherUser->avatar,
        ],
        'currentUserId' => $authId,
    ]);
}

}
