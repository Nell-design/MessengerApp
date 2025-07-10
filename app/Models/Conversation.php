<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['first_id', 'second_id'];

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

    public function getOtherUserAttribute()
    {
        return auth()->id() === $this->first_id
            ? $this->secondUser
            : $this->firstUser;
    }
}
