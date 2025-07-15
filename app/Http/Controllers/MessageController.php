<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\UserTypingEvent;
use App\Events\MessageSentEvent;
use App\Events\MessageReadEvent;
use App\Events\MessageDeletedEvent;
use App\Events\MessageReceivedEvent;
use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;

class MessageController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private MessageService $messageService
    ) {}

    /**
     * Récupérer tous les messages d'une conversation
     */
    public function index(Conversation $conversation)
    {
        // Charge les messages avec les relations sender et receiver
        $conversation->load('messages.sender', 'messages.receiver');

        // Formater chaque message pour inclure les avatars
        $messages = $conversation->messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'content' => $msg->is_deleted_for_everyone ? null : $msg->content,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'created_at' => $msg->created_at,
                'sender_avatar' => $msg->sender?->avatar ?? '/default-avatar.png',
                'receiver_avatar' => $msg->receiver?->avatar ?? '/default-avatar.png',
                'sender_name' => $msg->sender?->name ?? '',
                'is_deleted_for_everyone' => $msg->is_deleted_for_everyone,
            ];
        });

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Envoyer un nouveau message
     * @param StoreMessageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
public function store(StoreMessageRequest $request)
{
    try {
            // 1. Valider les données reçues (content, conversation_id)
        $validated = $request->validated();

            \Log::info('📤 MessageController: Début de l\'envoi de message', [
                'conversation_id' => $validated['conversation_id'],
                'content' => $validated['content'],
                'sender_id' => auth()->id(),
                'broadcast_default' => config('broadcasting.default')
            ]);

            // 2. Appeler le service pour créer et enregistrer le message
            // On force le sender_id à l'utilisateur authentifié pour la sécurité
        $message = $this->messageService->sendMessage([
            ...$validated,
                'sender_id' => auth()->id(),
            ]);

            \Log::info('✅ MessageController: Message créé avec succès', [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id
            ]);

            // 3. Diffuser l'événement MessageSentEvent pour le temps réel (broadcast)
            // broadcast(new MessageSentEvent($message))->toOthers();

            // 4. Retourner le message créé au frontend (201 = created)
        return response()->json($message, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
        \Log::error('Erreur de validation :', $e->errors());
        return response()->json([
            'errors' => $e->errors()
        ], 422);
        } catch (\Exception $e) {
            \Log::error('❌ MessageController: Erreur lors de l\'envoi de message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de l\'envoi du message'
            ], 500);
    }
}


    /**
     * Supprimer un message
     */
    public function destroy(Message $message, Request $request)
    {
        $this->authorize('delete', $message);

        $forEveryone = $request->boolean('for_everyone', false);

        \Log::info('🗑️ MessageController: Suppression de message', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'deleted_by' => auth()->id(),
            'for_everyone' => $forEveryone
        ]);

        $result = $this->messageService->deleteMessage(
            $message,
            $forEveryone
        );

        if ($result === 'too_late_for_everyone') {
            return response()->json([
                'error' => 'Le délai pour supprimer ce message pour tout le monde est dépassé.'
            ], 403);
        }

        broadcast(new MessageDeletedEvent(
            $message->conversation_id,
            $message->id,
            auth()->id()
        ))->toOthers();

        return response()->json([
            'status' => $result === 'permanent' ? 'permanently_deleted' : 'soft_deleted'
        ]);
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead(Message $message)
    {
        \Log::info('📖 MessageController: Tentative de marquage comme lu', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'is_read' => $message->is_read,
            'current_user_id' => auth()->id(),
            'can_view' => auth()->user()->can('view', $message)
        ]);

        try {
        $this->authorize('view', $message);

            $success = $this->messageService->markMessageAsRead($message);

            \Log::info('📖 MessageController: Résultat du marquage', [
                'message_id' => $message->id,
                'success' => $success
            ]);

            if ($success) {
                return response()->noContent();
            }

            return response()->json([
                'error' => 'Impossible de marquer le message comme lu'
            ], 400);
        } catch (\Exception $e) {
            \Log::error('📖 MessageController: Erreur lors du marquage comme lu', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors du marquage comme lu',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Marquer tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead(Conversation $conversation)
    {
        \Log::info('📖 MessageController: Marquage de tous les messages de la conversation comme lus', [
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id()
        ]);

        try {
            // Vérifier que l'utilisateur fait partie de la conversation
            if (auth()->id() !== $conversation->first_id && auth()->id() !== $conversation->second_id) {
                return response()->json([
                    'error' => 'Accès non autorisé à cette conversation'
                ], 403);
            }

            $success = $this->messageService->markConversationAsRead($conversation->id, auth()->id());

            if ($success) {
        return response()->noContent();
            }

            return response()->json([
                'error' => 'Impossible de marquer les messages comme lus'
            ], 400);
        } catch (\Exception $e) {
            \Log::error('📖 MessageController: Erreur lors du marquage de la conversation comme lue', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors du marquage comme lu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Indiquer l'état de frappe (typing)
     */
    public function typingStatus(Request $request)
    {
        try {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'is_typing' => 'required|boolean',
        ]);

            $conversationId = $request->input('conversation_id');
            $isTyping = $request->input('is_typing');
            $userId = auth()->id();

            \Log::info('⌨️ MessageController: Statut de frappe', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'is_typing' => $isTyping
            ]);

            event(new UserTypingEvent(
                $conversationId,
                $userId,
                $isTyping
            ));

        return response()->noContent();
        } catch (\Exception $e) {
            \Log::error('Erreur dans typingStatus:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'envoi du statut de frappe',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
