<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Créer une notification pour un nouveau message
     */
    public function createMessageNotification($message, $receiverId)
    {
        try {
            $notification = Notification::create([
                'user_id' => $receiverId,
                'type' => 'new_message',
                'data' => [
                    'message_id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'content' => $message->content,
                    'conversation_name' => $message->conversation->name ?? 'Nouveau message',
                ],
                'read_at' => null,
            ]);

            Log::info('Notification créée', [
                'notification_id' => $notification->id,
                'user_id' => $receiverId,
                'message_id' => $message->id
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la notification', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
                'receiver_id' => $receiverId
            ]);
            return null;
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($notificationId, $userId)
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de la notification comme lue', [
                'error' => $e->getMessage(),
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Marquer toutes les notifications d'une conversation comme lues
     */
    public function markConversationAsRead($conversationId, $userId)
    {
        try {
            $notifications = Notification::where('user_id', $userId)
                ->where('type', 'new_message')
                ->whereJsonContains('data->conversation_id', $conversationId)
                ->whereNull('read_at')
                ->get();

            foreach ($notifications as $notification) {
                $notification->markAsRead();
            }

            Log::info('Notifications marquées comme lues', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'count' => $notifications->count()
            ]);

            return $notifications->count();
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage des notifications comme lues', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
                'user_id' => $userId
            ]);
            return 0;
        }
    }

    /**
     * Obtenir le nombre de notifications non lues pour un utilisateur
     */
    public function getUnreadCount($userId)
    {
        try {
            return Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des notifications non lues', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return 0;
        }
    }

    /**
     * Obtenir le nombre de notifications non lues pour une conversation spécifique
     */
    public function getUnreadCountForConversation($conversationId, $userId)
    {
        try {
            return Notification::where('user_id', $userId)
                ->where('type', 'new_message')
                ->whereJsonContains('data->conversation_id', $conversationId)
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des notifications non lues pour la conversation', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
                'user_id' => $userId
            ]);
            return 0;
        }
    }
} 