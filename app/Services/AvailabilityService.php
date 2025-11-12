<?php

namespace App\Services;

use App\Models\AvailabilityForMeetup;
use App\Models\LookingFor;

class AvailabilityService
{
    public function getPaginated(?string $search = null)
    {
        $query = AvailabilityForMeetup::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return AvailabilityForMeetup::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = AvailabilityForMeetup::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = AvailabilityForMeetup::findOrFail($id);
        $breed->delete();
    }
}
