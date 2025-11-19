<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FriendSendRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'receiver_id' => 'required|integer|exists:users,id'

        ];
    }
}
