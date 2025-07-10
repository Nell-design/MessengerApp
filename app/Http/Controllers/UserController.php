<?php

namespace App\Http\Controllers;

use App\Events\UserStatusEvent;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean'
        ]);

        auth()->user()->update([
            'is_online' => $request->is_online,
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
            ->get(['id', 'name', 'avatar', 'is_online']);

        return response()->json($users);
    }
}