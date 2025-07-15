<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Events\MessageSentEvent;
use App\Events\MessageDeletedEvent;
use App\Events\MessageDeletedForEveryoneEvent;
use App\Events\MessageReceivedEvent;
use App\Events\ConversationListUpdatedEvent;
use App\Services\NotificationService;

class MessageService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Crée et enregistre un message dans une conversation
     * @param array $data (content, conversation_id, sender_id)
     * @return Message
     */
    public function sendMessage(array $data): Message
    {
        // 1. Récupérer la conversation cible
        $conversation = Conversation::findOrFail($data['conversation_id']);

        // 2. Déterminer l'autre utilisateur (le destinataire)
        $senderId = auth()->id(); // Sécurité : toujours l'utilisateur connecté
        $receiverId = ($conversation->first_id === $senderId) ? $conversation->second_id : $conversation->first_id;

        // dd([
        //     'content' => $data['content'],
        //     'conversation_id' => $conversation->id,
        //     'sender_id' => $senderId,
        //     'receiver_id' => $receiverId,
        //     'is_read' => false,
        //     'deleted_for_sender' => false,
        //     'deleted_for_receiver' => false,
        // ]);

        // 3. Créer le message en base de données
        $message = Message::create([
            'content' => $data['content'],
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'is_read' => false,
            'deleted_for_sender' => false,
            'deleted_for_receiver' => false,
        ]);
        // 4. Charger les relations nécessaires
        $message->load(['sender', 'conversation']);

        // 5. Créer une notification pour le destinataire
        $this->notificationService->createMessageNotification($message, $receiverId);

        // 6. Déclencher un événement pour le temps réel (notifications, broadcast)
        \Log::info('📤 MessageService: Déclenchement MessageSentEvent', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id
        ]);


        event(new MessageSentEvent($message));

        // 7. Mettre à jour la liste des conversations pour les deux utilisateurs
        \Log::info('🔄 MessageService: Déclenchement ConversationListUpdatedEvent', [
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'action' => 'updated'
        ]);

        event(new \App\Events\ConversationListUpdatedEvent($senderId, $conversation, 'updated'));
        event(new \App\Events\ConversationListUpdatedEvent($receiverId, $conversation, 'updated'));

        // 8. Log de confirmation
        \Log::info('✅ MessageService: Message envoyé avec succès', [
            'message_id' => $message->id,
            'content' => $message->content,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'events_triggered' => ['MessageSentEvent', 'ConversationListUpdatedEvent']
        ]);

        // 9. Retourner le message créé
        return $message;
    }

    public function deleteMessage(Message $message, bool $forEveryone = false): string
    {
        if ($forEveryone) {
            // Vérifie le délai (1h)
            if ($message->isDeletableForEveryone()) {
                $message->update([
                    'is_deleted_for_everyone' => true,
                    'content' => null, // On peut aussi mettre 'Ce message a été supprimé' côté frontend
                ]);
                event(new MessageDeletedForEveryoneEvent(
                    $message->conversation_id,
                    $message->id
                ));
                // Mettre à jour la liste des conversations
                $conversation = $message->conversation;
                event(new ConversationListUpdatedEvent($conversation->first_id, $conversation, 'updated'));
                event(new ConversationListUpdatedEvent($conversation->second_id, $conversation, 'updated'));
                return 'deleted_for_everyone';
            } else {
                return 'too_late_for_everyone';
            }
        }
        $result = $message->deleteForUser(auth()->id());
        event(new MessageDeletedEvent(
            $message->conversation_id,
            $message->id,
            auth()->id()
        ));
        // Mettre à jour la liste des conversations
        $conversation = $message->conversation;
        event(new ConversationListUpdatedEvent($conversation->first_id, $conversation, 'updated'));
        event(new ConversationListUpdatedEvent($conversation->second_id, $conversation, 'updated'));
        return $result;
    }

    /**
     * Marquer un message comme lu
     */
    public function markMessageAsRead(Message $message): bool
    {
        try {
            if (!$message->is_read && $message->receiver_id === auth()->id()) {
                $message->update(['is_read' => true]);

                // Déclencher l'événement de réception
                event(new MessageReceivedEvent($message));

                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Erreur lors du marquage comme lu', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
                'user_id' => auth()->id()
            ]);
            return false;
        }
    }

    /**
     * Marquer tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead(int $conversationId, int $userId): bool
    {
        try {
            // Marquer les messages comme lus
            $unreadMessages = Message::where('conversation_id', $conversationId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->get();

            foreach ($unreadMessages as $message) {
                $message->update(['is_read' => true]);
                // Déclencher l'événement pour chaque message marqué comme lu
                event(new MessageReceivedEvent($message));
            }

            // Marquer les notifications comme lues
            $this->notificationService->markConversationAsRead($conversationId, $userId);

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur lors du marquage comme lu', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
                'user_id' => $userId
            ]);
            return false;
        }
    }
}