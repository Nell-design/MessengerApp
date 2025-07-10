<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de 10 utilisateurs fictifs
        User::factory(10)->create([
            'password' => Hash::make('password'),
            'avatar' => function() {
                return 'https://i.pravatar.cc/300?u=' . uniqid();
            },
        ]);
 
        // Création d'un utilisateur de test
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'avatar' => 'https://i.pravatar.cc/300?u=test',
            'last_connexion' => now()
        ]);
    }
}
