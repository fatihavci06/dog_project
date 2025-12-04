<?php
namespace App\Services;

use App\Models\Screen;

class ScreenService
{
    public function getAll()
    {
        return Screen::all();
    }

    public function getById($id)
    {
        return Screen::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $screen = Screen::findOrFail($id);
        $screen->content = $data['content'];
        $screen->save();
        return $screen;
    }
}
