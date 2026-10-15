<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DevoirFileValidator
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx'];

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(
        private readonly int $maxUploadBytes,
    ) {
    }

    public function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new BadRequestHttpException('Fichier invalide ou erreur lors de l\'envoi.');
        }

        $size = $file->getSize();
        if ($size === false || $size <= 0) {
            throw new BadRequestHttpException('Fichier vide.');
        }

        if ($size > $this->maxUploadBytes) {
            throw new BadRequestHttpException(sprintf(
                'Fichier trop volumineux (max %d octets).',
                $this->maxUploadBytes,
            ));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!\in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new BadRequestHttpException('Format non autorisé. Formats acceptés : PDF, DOC, DOCX.');
        }

        $mime = $file->getMimeType() ?? '';
        if ($mime !== '' && !\in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new BadRequestHttpException('Type MIME non autorisé. Formats acceptés : PDF, Word.');
        }
    }

    public function resolveMimeType(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        if (\is_string($mime) && $mime !== '' && \in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            return $mime;
        }

        return match (strtolower($file->getClientOriginalExtension())) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}
