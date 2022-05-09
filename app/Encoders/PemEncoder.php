<?php

namespace App\Encoders;

class PemEncoder
{
    public static function encode(string $data): string
    {
        return trim($data) . "\n";
    }
}
