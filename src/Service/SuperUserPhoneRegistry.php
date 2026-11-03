<?php

namespace App\Service;

use App\Util\PhoneNormalizer;

final class SuperUserPhoneRegistry
{
    /** @var string[] national phone numbers */
    private array $phones;

    public function __construct(?string $phones = null)
    {
        $this->phones = $this->parsePhones($phones ?? '');
    }

    public function isSuperUser(string $telephone): bool
    {
        $national = PhoneNormalizer::toNational($telephone);
        if ($national === '') {
            return false;
        }

        foreach ($this->phones as $configured) {
            if (PhoneNormalizer::matches($telephone, $configured)
                || $national === $configured
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function getConfiguredPhones(): array
    {
        return $this->phones;
    }

    /**
     * @return string[]
     */
    private function parsePhones(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        // Accepte CSV, points-virgules ou retours à la ligne
        $parts = preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $phones = [];
        foreach ($parts as $raw) {
            $national = PhoneNormalizer::toNational(trim($raw, " \t\"'"));
            if ($national !== '') {
                $phones[] = $national;
            }
        }

        return array_values(array_unique($phones));
    }
}
