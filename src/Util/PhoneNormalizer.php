<?php

namespace App\Util;

final class PhoneNormalizer
{
    private const COUNTRY_CODE = '243';

    public static function normalize(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?? '';
    }

    /**
     * Numéro national sans indicatif (ex. 997031460 pour 0997031460 ou 243997031460).
     */
    public static function toNational(string $phone): string
    {
        $digits = self::normalize($phone);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, self::COUNTRY_CODE) && strlen($digits) > strlen(self::COUNTRY_CODE)) {
            return substr($digits, strlen(self::COUNTRY_CODE));
        }

        if (str_starts_with($digits, '0') && strlen($digits) > 1) {
            return substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Formes comparables pour un numéro (243XXXXXXXX, 0XXXXXXXX, XXXXXXXXX).
     *
     * @return string[]
     */
    public static function variants(string $phone): array
    {
        $digits = self::normalize($phone);
        if ($digits === '') {
            return [];
        }

        $national = self::toNational($phone);
        $variants = [$digits];

        if ($national !== '') {
            $variants[] = $national;
            $variants[] = '0'.$national;
            $variants[] = self::COUNTRY_CODE.$national;
        }

        return array_values(array_unique($variants));
    }

    public static function matches(string $phoneA, string $phoneB): bool
    {
        $nationalA = self::toNational($phoneA);
        $nationalB = self::toNational($phoneB);

        if ($nationalA === '' || $nationalB === '') {
            return false;
        }

        return $nationalA === $nationalB;
    }
}
