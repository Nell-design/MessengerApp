<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Obtenir toutes les notifications de l'utilisateur
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'notifications' => $notifications->items(),
                'total' => $notifications->total(),
                'unread_count' => $this->notificationService->getUnreadCount($userId)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, int $notificationId): JsonResponse
    {
        try {
            $userId = auth()->id();
            $success = $this->notificationService->markAsRead($notificationId, $userId);

            if ($success) {
                return response()->json([
                    'message' => 'Notification marquée comme lue',
                    'unread_count' => $this->notificationService->getUnreadCount($userId)
                ]);
            }

            return response()->json([
                'error' => 'Notification non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du marquage de la notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $count = Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'message' => 'Toutes les notifications marquées comme lues',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du marquage des notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer les notifications d'une conversation comme lues
     */
    public function markConversationAsRead(Request $request, int $conversationId): JsonResponse
    {
        try {
            $userId = auth()->id();
            $count = $this->notificationService->markConversationAsRead($conversationId, $userId);

            return response()->json([
                'message' => 'Notifications de la conversation marquées comme lues',
                'count' => $count,
                'unread_count' => $this->notificationService->getUnreadCount($userId)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du marquage des notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $count = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'unread_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du comptage des notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 