<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                'different:'.auth()->id(),
                function ($attribute, $value, $fail) {
                    if (Conversation::betweenUsers(auth()->id(), $value)->exists()) {
                        $fail('Une conversation existe déjà avec cet utilisateur.');
                    }
                }
            ]
        ];
    }
}