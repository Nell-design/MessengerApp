<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'content',
        'conversation_id',
        'sender_id',
        'receiver_id',
        'is_read',
        'deleted_for_sender',
        'deleted_for_receiver'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'deleted_for_sender' => 'boolean',
        'deleted_for_receiver' => 'boolean'
    ];

    // Relations
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->where('deleted_for_sender', false);
        })->orWhere(function($q) use ($userId) {
            $q->where('receiver_id', $userId)
              ->where('deleted_for_receiver', false);
        });
    }

    // Méthodes
    public function markAsRead()
    {
        if (!$this->is_read && $this->receiver_id === auth()->id()) {
            $this->update(['is_read' => true]);
        }
    }

    public function deleteForUser($userId)
    {
        if ($this->sender_id === $userId) {
            $this->update(['deleted_for_sender' => true]);
        } else {
            $this->update(['deleted_for_receiver' => true]);
        }

        // Suppression réelle si les deux ont supprimé
        if ($this->deleted_for_sender && $this->deleted_for_receiver) {
            $this->delete();
            return 'permanent';
        }

        return 'soft';
    }
}