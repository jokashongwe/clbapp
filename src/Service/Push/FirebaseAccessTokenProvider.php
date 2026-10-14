<?php

namespace App\Service\Push;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FirebaseAccessTokenProvider
{
    private ?string $accessToken = null;
    private int $accessTokenExpiresAt = 0;

    /** @var array<string, mixed>|null */
    private ?array $credentials = null;

    private readonly string $credentialsPath;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'FIREBASE_CREDENTIALS')]
        string $credentialsPath,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        $this->credentialsPath = $this->resolveCredentialsPath($credentialsPath, $projectDir);
    }

    public function isConfigured(): bool
    {
        return $this->credentialsPath !== '' && is_readable($this->credentialsPath);
    }

    public function getProjectId(): ?string
    {
        $credentials = $this->loadCredentials();
        if ($credentials === null) {
            return null;
        }

        $projectId = $credentials['project_id'] ?? null;

        return \is_string($projectId) && $projectId !== '' ? $projectId : null;
    }

    public function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $now = time();
        if ($this->accessToken !== null && $this->accessTokenExpiresAt > $now + 60) {
            return $this->accessToken;
        }

        $credentials = $this->loadCredentials();
        if ($credentials === null) {
            return null;
        }

        $clientEmail = $credentials['client_email'] ?? null;
        $privateKey = $credentials['private_key'] ?? null;
        if (!\is_string($clientEmail) || !\is_string($privateKey) || $privateKey === '') {
            return null;
        }

        $jwt = $this->createSignedJwt($clientEmail, $privateKey);
        if ($jwt === null) {
            return null;
        }

        $response = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        /** @var array<string, mixed> $data */
        $data = $response->toArray(false);
        $token = $data['access_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 3600;

        if (!\is_string($token) || $token === '') {
            return null;
        }

        $this->accessToken = $token;
        $this->accessTokenExpiresAt = $now + (is_numeric($expiresIn) ? (int) $expiresIn : 3600);

        return $this->accessToken;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadCredentials(): ?array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        if (!$this->isConfigured()) {
            return null;
        }

        $json = file_get_contents($this->credentialsPath);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!\is_array($data)) {
            return null;
        }

        $this->credentials = $data;

        return $this->credentials;
    }

    private function createSignedJwt(string $clientEmail, string $privateKey): ?string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $signature = '';
        $ok = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            return null;
        }

        return $unsigned.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function resolveCredentialsPath(string $path, string $projectDir): string
    {
        if ($path === '') {
            return '';
        }

        if (is_readable($path)) {
            return $path;
        }

        $relative = $projectDir.'/'.ltrim(str_replace('\\', '/', $path), '/');
        if (is_readable($relative)) {
            return $relative;
        }

        return $path;
    }
}
