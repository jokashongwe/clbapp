<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class CloudflareR2Storage
{
    private ?S3Client $client = null;

    public function __construct(
        #[Autowire(env: 'R2_ACCOUNT_ID')]
        private readonly string $accountId,
        #[Autowire(env: 'R2_ACCESS_KEY_ID')]
        private readonly string $accessKeyId,
        #[Autowire(env: 'R2_SECRET_ACCESS_KEY')]
        private readonly string $secretAccessKey,
        #[Autowire(env: 'R2_BUCKET')]
        private readonly string $bucket,
        #[Autowire(env: 'int:R2_PRESIGNED_URL_TTL')]
        private readonly int $presignedUrlTtlSeconds,
    ) {
    }

    public function upload(string $storageKey, string $contents, string $contentType): void
    {
        $this->getClient()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $storageKey,
            'Body' => $contents,
            'ContentType' => $contentType,
        ]);
    }

    public function createPresignedDownloadUrl(string $storageKey, string $downloadFilename): string
    {
        $command = $this->getClient()->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $storageKey,
            'ResponseContentDisposition' => sprintf(
                'attachment; filename="%s"',
                addslashes($downloadFilename),
            ),
        ]);

        $request = $this->getClient()->createPresignedRequest(
            $command,
            sprintf('+%d seconds', $this->presignedUrlTtlSeconds),
        );

        return (string) $request->getUri();
    }

    public function getPresignedUrlTtlSeconds(): int
    {
        return $this->presignedUrlTtlSeconds;
    }

    private function getClient(): S3Client
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if ($this->accountId === '' || $this->accessKeyId === '' || $this->secretAccessKey === '' || $this->bucket === '') {
            throw new ServiceUnavailableHttpException(null, 'Stockage Cloudflare R2 non configuré.');
        }

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => sprintf('https://%s.r2.cloudflarestorage.com', $this->accountId),
            'credentials' => [
                'key' => $this->accessKeyId,
                'secret' => $this->secretAccessKey,
            ],
            'use_path_style_endpoint' => true,
        ]);

        return $this->client;
    }
}
