<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Conversation extends Model
{
    protected $fillable = [
        'first_id',
        'second_id'
    ];

    // Relations
    public function firstUser()
    {
        return $this->belongsTo(User::class, 'first_id');
    }

   /*  public function startConversation(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    $authId = Auth::id();
    $otherId = $request->user_id;

    // Vérifier si une conversation existe déjà
    $conversation = Conversation::betweenUsers($authId, $otherId)->first();

    if (!$conversation) {
        // Ordre constant pour éviter les doublons
        [$firstId, $secondId] = $authId < $otherId
            ? [$authId, $otherId]
            : [$otherId, $authId];

        $conversation = Conversation::create([
            'first_id' => $firstId,
            'second_id' => $secondId
        ]);
    }

    $otherUser = $conversation->otherUser;

    return response()->json([
        'id' => $conversation->id,
        'name' => $otherUser->name,
        'avatar' => $otherUser->avatar,
        'message' => optional($conversation->lastMessage)->content ?? '',
        'time' => optional($conversation->lastMessage)->created_at?->diffForHumans() ?? 'Maintenant',
    ]);
} */

    public function secondUser()
    {
        return $this->belongsTo(User::class, 'second_id');
    }

    public function lastMessage()
{
    return $this->hasOne(Message::class)->latestOfMany();
}


    // Scopes
    public function scopeBetweenUsers($query, $firstId, $secondId)
    {
        return $query->where(function($q) use ($firstId, $secondId) {
            $q->where('first_id', $firstId)
              ->where('second_id', $secondId);
        })->orWhere(function($q) use ($firstId, $secondId) {
            $q->where('first_id', $secondId)
              ->where('second_id', $firstId);
        });
    }

    public function messages()
{
    return $this->hasMany(Message::class);
}


    // Accessors
    public function getOtherUserAttribute()
    {
        return auth()->id() === $this->first_id
            ? $this->secondUser
            : $this->firstUser;
    }

    public function otherUser()
{
    return $this->belongsTo(User::class, $this->first_id === auth()->id() ? 'second_id' : 'first_id');
}


    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
}