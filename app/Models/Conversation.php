<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function secondUser()
    {
        return $this->belongsTo(User::class, 'second_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
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

    // Accessors
    public function getOtherUserAttribute()
    {
        return auth()->id() === $this->first_id 
            ? $this->secondUser 
            : $this->firstUser;
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
}