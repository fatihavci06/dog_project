<?php

namespace App\Services;

use App\Models\Language;

class GenericMultilangService
{
    protected $model;

    public function __construct($modelClass)
    {
        $this->model = new $modelClass();
    }
    public function listForApi($locale)
    {
        return $this->model->all()->map(function ($item) use ($locale) {

            // Temel alanlar
            $data = [
                'id'   => $item->id,
                'name' => $item->translate('name', $locale),
            ];

            // 🔥 Eğer model Bread ise icon eklensin
            if ($item instanceof \App\Models\Vibe) {
                // Modelde getIconPathAttribute varsa direkt icon_path kullanılır
                $data['icon'] = $item->icon_path;
            }

            return $data;
        });
    }


    public function all($locale)
    {
        return $this->model->all()->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->translate('name', $locale)
        ]);
    }

    public function find($id, $locale)
    {
        $item = $this->model->findOrFail($id);

        return [
            'id' => $item->id,
            'name' => $item->translate('name', $locale)
        ];
    }

    public function create(array $names)
    {
        $item = $this->model->create();

        foreach ($names as $locale => $value) {
            $item->setTranslation('name', $locale, $value);
        }

        return $item;
    }

    public function update($id, array $names)
    {
        $item = $this->model->findOrFail($id);

        foreach ($names as $locale => $value) {
            $item->setTranslation('name', $locale, $value);
        }

        return $item;
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }
}
