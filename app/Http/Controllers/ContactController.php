<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = User::where('id', '!=', auth()->id())
            ->with(['lastMessageWithAuthUser'])
            ->orderBy('name')
            ->get();

        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $contact = User::where('email', $request->email)->firstOrFail();

        // Vérifier si la conversation existe déjà
        $existingConversation = Conversation::betweenUsers(auth()->id(), $contact->id)->first();

        if ($existingConversation) {
            return response()->json([
                'status' => 'exists',
                'conversation' => $existingConversation
            ]);
        }

        $conversation = Conversation::create([
            'first_id' => auth()->id(),
            'second_id' => $contact->id
        ]);

        return response()->json([
            'status' => 'created',
            'conversation' => $conversation
        ], 201);
    }
}