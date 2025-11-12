<?php

namespace App\Services;

use App\Models\HealthInfo;
use App\Models\TravelRadius;

class TravelRadiusService
{
    public function getPaginated(?string $search = null)
    {
        $query = TravelRadius::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return TravelRadius::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = TravelRadius::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = TravelRadius::findOrFail($id);
        $breed->delete();
    }
}
