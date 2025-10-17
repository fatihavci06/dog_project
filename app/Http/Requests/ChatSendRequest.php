<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatSendRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'to_user_id' => 'required|integer|exists:users,id',
            'body' => 'nullable|string',
        ];
    }
}
