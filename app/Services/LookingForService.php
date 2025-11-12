<?php

namespace App\Services;

use App\Models\LookingFor;

class LookingForService
{
    public function getPaginated(?string $search = null)
    {
        $query = LookingFor::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return LookingFor::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = LookingFor::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = LookingFor::findOrFail($id);
        $breed->delete();
    }
}
