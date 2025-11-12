<?php

namespace App\Services;

use App\Models\AgeRange;

class AgeRangeService
{
    public function getPaginated(?string $search = null)
    {
        $query = AgeRange::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return AgeRange::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = AgeRange::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = AgeRange::findOrFail($id);
        $breed->delete();
    }
}
