<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

abstract class AbstractApiController extends AbstractController
{
    protected function jsonRead(mixed $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status, [], [
            AbstractNormalizer::GROUPS => ['read'],
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => static fn (): string => '',
        ]);
    }
}
