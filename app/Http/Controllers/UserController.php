<?php

namespace App\Http\Controllers;

use App\Events\UserStatusEvent;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

public function listAll()
{
    if (!auth()->check()) {
        \Log::error('Tentative d\'accès non authentifiée à /users/all');
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    $users = User::where('id', '!=', auth()->id())
        ->get(['id', 'name', 'avatar']);

    \Log::info('Utilisateurs récupérés :', $users->toArray());

    // Ne pas mettre de fallback, juste renvoyer l'avatar tel quel (null si absent)
    return response()->json($users);
}


    public function updateStatus(Request $request)
    {
        auth()->user()->update([
            'last_connexion' => now()
        ]);

        broadcast(new UserStatusEvent(auth()->id(), $request->is_online));

        return response()->json(['status' => 'success']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $users = User::where('name', 'like', '%'.$request->query.'%')
            ->where('id', '!=', auth()->id())
            ->limit(10)
            ->get(['id', 'name', 'avatar']);

        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar, // null si absent
        ]);
    }
}
