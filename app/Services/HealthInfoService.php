<?php

namespace App\Services;

use App\Models\HealthInfo;
use App\Models\LookingFor;

class HealthInfoService
{
    public function getPaginated(?string $search = null)
    {
        $query = HealthInfo::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return HealthInfo::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = HealthInfo::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = HealthInfo::findOrFail($id);
        $breed->delete();
    }
}
