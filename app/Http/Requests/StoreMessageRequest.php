<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Conversation;
        
class StoreMessageRequest extends FormRequest
{
    public function rules()
    {
        return [
            'conversation_id' => 'required|exists:conversations,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:2000'
        ];
    }

    public function authorize()
    {
        return Conversation::where('id', $this->conversation_id)
            ->where(function($q) {
                $q->where('first_id', auth()->id())
                  ->orWhere('second_id', auth()->id());
            })->exists();
    }
}