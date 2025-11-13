<?php

namespace App\Services;

use App\Models\PageInfo;

class PageInfoService
{
    public function all()
    {
        return PageInfo::with('translations.language')->get();
    }

    public function create(array $data)
    {
        $page = PageInfo::create([
            'page_name' => $data['page_name'],
            'image_path' => $data['image_path'] ?? null,
        ]);

        foreach ($data['title'] as $locale => $value) {
            $page->setTranslation('title', $locale, $value);
        }

        foreach ($data['description'] as $locale => $value) {
            $page->setTranslation('description', $locale, $value);
        }

        return $page;
    }

    public function update($id, array $data)
    {
        $page = PageInfo::findOrFail($id);



        if (isset($data['image_path'])) {
            $page->image_path = $data['image_path'];
        }

        $page->save();

        foreach ($data['title'] as $locale => $value) {
            $page->setTranslation('title', $locale, $value);
        }

        foreach ($data['description'] as $locale => $value) {
            $page->setTranslation('description', $locale, $value);
        }

        return $page;
    }
}
