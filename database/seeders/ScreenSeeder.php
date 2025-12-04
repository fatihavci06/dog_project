<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;

class ScreenSeeder extends Seeder
{
    public function run()
    {
        Screen::updateOrCreate(
            ['screen_slug' => 'chat_empty'],
            [
                'content' => [
                    "hero_image" => [
                        "visible" => true,
                        "url" => "https://cdn.example.com/images/dog-hero.jpg",
                        "position" => "top",
                        "style" => [
                            "aspect_ratio" => 1.2,
                            "resize_mode" => "cover",
                            "corner_radius" => 20
                        ]
                    ],
                    "text_group" => [
                        "visible" => true,
                        "position_on_screen" => "center",
                        "text_align" => "center",
                        "overlay_on_image" => false,
                        "title" => [
                            "text" => "WoofDate Awaits!!",
                            "color" => "#333333",
                            "font_size" => 28,
                            "font_weight" => "bold"
                        ],
                        "subtitle" => [
                            "text" => "Connect with local dog owners for playdates...",
                            "color" => "#666666",
                            "font_size" => 16,
                            "margin_top" => 12
                        ]
                    ],
                    "cta_button" => [
                        "visible" => true,
                        "text" => "Get Started",
                        "action_type" => "navigate",
                        "action_value" => "NewChatPage",
                        "theme_id" => 1,
                        "show_icon" => true
                    ]
                ]
            ]
        );
    }
}
