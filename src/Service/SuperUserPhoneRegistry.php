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

        return \in_array($national, $this->phones, true);
    }

    /**
     * @return string[]
     */
    private function parsePhones(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $phones = [];
        foreach (explode(',', $value) as $raw) {
            $national = PhoneNormalizer::toNational(trim($raw));
            if ($national !== '') {
                $phones[] = $national;
            }
        }

        return array_values(array_unique($phones));
    }
}
