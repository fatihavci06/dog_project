<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'one_signal_player_id' => $this->one_signal_player_id,
            'location_city' => $this->location_city,
            'location_district' => $this->location_district,
            'biography' => $this->biography,
            'photo_url' => $this->photo_url, // modelde accessor ile URL dönüyor
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,

            'tests' => $this->testUserRoles->map(function($test) {
                return [
                    'id' => $test->id,
                    // sadece dog bilgisi: id ve name
                    'dog' => $test->dog
                ];
            }),
        ];
    }
}
