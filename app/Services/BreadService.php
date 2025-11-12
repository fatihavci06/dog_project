<?php

namespace App\Services;

use App\Models\Bread;
use App\Models\Breed;

class BreadService
{
    public function getPaginated(?string $search = null)
    {
        $query = Bread::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return Bread::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = Bread::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = Bread::findOrFail($id);
        $breed->delete();
    }
}
