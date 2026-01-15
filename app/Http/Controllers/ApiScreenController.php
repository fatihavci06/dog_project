<?php

namespace App\Http\Controllers;

use App\Services\ScreenService;
use Illuminate\Http\Request;

class ApiScreenController extends ApiController
{
    public function __construct(private ScreenService $service) {}
    public function getScreen($id, $language='en')
    {
        return[
            "data" => $this->service->getById($id, $language)
        ];
        // Burada ekran kayıtlarını ID'ye göre eşliyoruz
        // $screens = [
        //     "1" => [
        //         "screen_id"   => "1",
        //         "screen_slug" => "chat_empty",
        //         "content"     => [
        //             "hero_image" => [
        //                 "visible"  => true,
        //                 "url"      => "https://cdn.example.com/images/dog-hero.jpg",
        //                 "position" => "top",
        //                 "style"    => [
        //                     "aspect_ratio"  => 1.2,
        //                     "resize_mode"   => "cover",
        //                     "corner_radius" => 20
        //                 ]
        //             ],

        //             "text_group" => [
        //                 "visible"            => true,
        //                 "position_on_screen" => "center",
        //                 "text_align"         => "center",
        //                 "overlay_on_image"   => false,

        //                 "title" => [
        //                     "text"        => "WoofDate Awaits!!",
        //                     "color"       => "#333333",
        //                     "font_size"   => 28,
        //                     "font_weight" => "bold"
        //                 ],

        //                 "subtitle" => [
        //                     "text"       => "Connect with local dog owners for playdates...",
        //                     "color"      => "#666666",
        //                     "font_size" => 16,
        //                     "margin_top" => 12
        //                 ]
        //             ],

        //             "cta_button" => [
        //                 "visible"      => true,
        //                 "text"         => "Get Started",
        //                 "action_type"  => "navigate",
        //                 "action_value" => "NewChatPage",
        //                 "theme_id"     => 1,
        //                 "show_icon"    => true
        //             ]
        //         ]
        //     ]
        // ];

        // // ID bulunamazsa
        // if (!isset($screens[$id])) {
        //     return response()->json([
        //         "message" => "Screen not found"
        //     ], 404);
        // }

        // // ID’ye göre ekranı döndür
        // return response()->json($screens[$id]);
    }
}
