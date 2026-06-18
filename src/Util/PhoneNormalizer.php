<?php

namespace App\Util;

final class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?? '';
    }
}
