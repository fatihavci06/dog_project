<?php

namespace App\Services;

use App\Models\MobileAppChatMessage;

class MobileAppChatMessageService
{
    public function all()
    {
        return MobileAppChatMessage::with('translations.language')
            ->orderBy('order', 'asc') // Artık 'order' alanına göre sıralanacak
            ->get();
    }

    public function store(array $data): MobileAppChatMessage
    {
        // En yüksek sıra numarasını bulup 1 ekliyoruz
        $maxOrder = MobileAppChatMessage::max('order') ?? 0;

        $chatMessage = MobileAppChatMessage::create([
            'type'  => $data['type'],
            'order' => $maxOrder + 1
        ]);

        foreach ($data['content'] as $locale => $value) {
            $chatMessage->setTranslation('content', $locale, $value);
        }

        return $chatMessage;
    }

    public function update(int $id, array $data): MobileAppChatMessage
    {
        $chatMessage = MobileAppChatMessage::findOrFail($id);

        $chatMessage->update([
            'type' => $data['type']
        ]);

        // Çevirileri güncelle
        foreach ($data['content'] as $locale => $value) {
            $chatMessage->setTranslation('content', $locale, $value);
        }

        return $chatMessage;
    }
    public function updateOrder(array $orders): void
    {
        foreach ($orders as $index => $id) {
            MobileAppChatMessage::where('id', $id)->update(['order' => $index + 1]);
        }
    }

    public function delete(int $id): void
    {
        $chatMessage = MobileAppChatMessage::findOrFail($id);
        // Eğer trait'in çevirileri otomatik siliyorsa ekstra bir şeye gerek yok.
        // Silmiyorsa önce çevirileri silmek gerekebilir: $chatMessage->translations()->delete();
        $chatMessage->delete();
    }
    public function getSuggestionsForApi(string $locale): array
    {
        // Verileri sırasına (order) göre çekiyoruz
        $messages = MobileAppChatMessage::orderBy('order', 'asc')->get();

        $suggestions = $messages->map(function ($item) use ($locale) {
            // İstenen JSON yapısındaki ID formatını oluşturuyoruz (örn: intro_1, question_2)


            // İstenen dildeki çeviriyi alıyoruz
            $text = $item->translate('content', $locale);

            // Eğer o dilde bir çeviri girilmemişse, boş dönmemesi için İngilizceyi (en) varsayılan yapabiliriz
            if (empty($text)) {
                $text = $item->translate('content', 'en');
            }

            return [
                'id'   => $item->id,
                'type' => $item->type,
                'text' => $text,
            ];
        });

        // İstenen "chatSuggestions" root objesi içinde döndürüyoruz
        return [
            'chatSuggestions' => $suggestions
        ];
    }
}
