<?php

namespace Database\Seeders;
 
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;
 
class MessagesTableSeeder extends Seeder
{
    private $messages = [
        "Salut, ça va ?",
        "Tu fais quoi ce week-end ?",
        "On se voit demain ?",
        "Je t'envoie le fichier",
        "Merci pour ton aide !",
        "Tu as vu le dernier épisode ?",
        "Je suis en retard désolé",
        "On se donne rendez-vous où ?",
        "Je suis occupé pour le moment",
        "Bonne journée !"
    ];
 
    public function run()
    {
        $conversations = Conversation::all();
 
        foreach ($conversations as $conversation) {
            // Chaque conversation aura 5-15 messages
            $numberOfMessages = rand(5, 15);
            for ($i = 0; $i < $numberOfMessages; $i++) {
                // Alterner entre les deux utilisateurs
                $sender = $i % 2 === 0 
                    ? $conversation->first_id 
                    : $conversation->second_id;
                $receiver = $sender === $conversation->first_id 
                    ? $conversation->second_id 
                    : $conversation->first_id;
 
                Message::create([
                    'content' => $this->messages[array_rand($this->messages)],
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender,
                    'receiver_id' => $receiver,
                    'is_read' => (bool)rand(0, 1),
                    'created_at' => now()
                        ->subDays(rand(0, 29))
                        ->subHours(rand(1, 24))
                        ->subMinutes(rand(1, 60))
                ]);
            }
        }
    }
}