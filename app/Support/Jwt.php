<?php

namespace App\Support;

class Jwt
{
    public static function secret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? null;

        if (!$secret) {
            throw new \RuntimeException('JWT_SECRET not defined');
        }

        return $secret;
    }

    public static function algo(): string
    {
        return 'HS256';
    }
}
