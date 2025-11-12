<?php

namespace App\Services;

use App\Models\Vibe;

class VibeService
{
    public function getPaginated(?string $search = null)
    {
        $query = Vibe::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    public function create(array $data)
    {
        return Vibe::create($data);
    }

    public function update(int $id, array $data)
    {
        $breed = Vibe::findOrFail($id);
        $breed->update($data);
        return $breed;
    }
    public function delete(int $id)
    {

        $breed = Vibe::findOrFail($id);
        $breed->delete();
    }
}
