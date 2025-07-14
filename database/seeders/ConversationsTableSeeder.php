<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConversationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
 
        // Création de conversations aléatoires
        foreach ($users as $user) {
            // Chaque utilisateur aura 3-5 conversations
            $numberOfConversations = rand(3, 5);
            for ($i = 0; $i < $numberOfConversations; $i++) {
                $otherUser = $users->where('id', '!=', $user->id)->random();
                // Vérifie si la conversation existe déjà
                $exists = Conversation::where(function($query) use ($user, $otherUser) {
                    $query->where('first_id', $user->id)
                          ->where('second_id', $otherUser->id);
                })->orWhere(function($query) use ($user, $otherUser) {
                    $query->where('first_id', $otherUser->id)
                          ->where('second_id', $user->id);
                })->exists();
 
                if (!$exists) {
                    Conversation::create([
                        'first_id' => $user->id,
                        'second_id' => $otherUser->id,
                        'created_at' => now()->subDays(rand(1, 30))
                    ]);
                }
            }
        }
    }
}
