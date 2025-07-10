<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'last_connexion',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_connexion' => 'datetime',
    ];

    // Relations
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'first_id')
            ->orWhere(function($query) {
                $query->where('second_id', $this->id);
            });
    }

    // Méthodes utilitaires
    public function getConversationWith($userId)
    {
        return Conversation::where(function($query) use ($userId) {
            $query->where('first_id', $this->id)
                  ->where('second_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('first_id', $userId)
                  ->where('second_id', $this->id);
        })->first();
    }
}