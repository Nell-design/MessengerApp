<?php
 
namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class User extends Authenticatable
{
    use Notifiable;
 
    protected $fillable = [
        'name', 'email', 'password', 'last_connexion', 'avatar'
    ];
 
    protected $casts = [
        'last_connexion' => 'datetime'
    ];
 
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'first_id')
            ->orWhere('second_id', $this->id);
    }
 
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
 
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}