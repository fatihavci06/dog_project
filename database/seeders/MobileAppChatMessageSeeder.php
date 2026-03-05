<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MobileAppChatMessage;

class MobileAppChatMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Önceki kayıtları temizlemek istersen (opsiyonel)
        // MobileAppChatMessage::truncate();

        // Tam olarak JSON'da istenen 5 hazır mesaj/soru
        $data = [
            [
                'type'  => 'message',
                'order' => 1,
                'translations' => [
                    'en' => 'Hey! Great to connect.',
                    'tr' => 'Hey! Seninle bağlantı kurmak harika.',
                ]
            ],
            [
                'type'  => 'question',
                'order' => 2,
                'translations' => [
                    'en' => 'What is your pup’s favourite walking spot?',
                    'tr' => 'Köpeğinin en sevdiği yürüyüş rotası neresi?',
                ]
            ],
            [
                'type'  => 'question',
                'order' => 3,
                'translations' => [
                    'en' => 'How is your pup with new walking friends?',
                    'tr' => 'Köpeğin yeni yürüyüş arkadaşlarıyla nasıl anlaşıyor?',
                ]
            ],
            [
                'type'  => 'question',
                'order' => 4,
                'translations' => [
                    'en' => 'Would you like to meet for a walkie sometime?',
                    'tr' => 'Bir ara yürüyüş için buluşmak ister misin?',
                ]
            ],
            [
                'type'  => 'question',
                'order' => 5,
                'translations' => [
                    'en' => 'Anything dog-friendly you planned for the weekend?',
                    'tr' => 'Hafta sonu için köpek dostu bir planın var mı?',
                ]
            ],
        ];

        // Döngüyle veritabanına ekle
        foreach ($data as $item) {
            $chatMessage = MobileAppChatMessage::create([
                'type'  => $item['type'],
                'order' => $item['order'],
            ]);

            // Çok dilli yapını kullanarak çevirileri kaydet
            foreach ($item['translations'] as $locale => $text) {
                $chatMessage->setTranslation('content', $locale, $text);
            }
        }
    }
}
