<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Les utilisateurs peuvent voir la liste des messages de leurs conversations
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): bool
    {
        // L'utilisateur peut voir le message s'il est l'expéditeur ou le destinataire
        return $user->id === $message->sender_id || $user->id === $message->receiver_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Les utilisateurs authentifiés peuvent créer des messages
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        // Seul l'expéditeur peut modifier son message
        return $user->id === $message->sender_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        // L'utilisateur peut supprimer le message s'il est l'expéditeur ou le destinataire
        // (pour "supprimer pour moi" vs "supprimer pour tout le monde")
        return $user->id === $message->sender_id || $user->id === $message->receiver_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): bool
    {
        // Seul l'expéditeur peut restaurer son message
        return $user->id === $message->sender_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        // Seul l'expéditeur peut supprimer définitivement son message
        return $user->id === $message->sender_id;
    }
}
