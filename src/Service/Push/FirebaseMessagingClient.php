<?php

namespace App\Service\Push;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FirebaseMessagingClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly FirebaseAccessTokenProvider $accessTokenProvider,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->accessTokenProvider->isConfigured()
            && $this->accessTokenProvider->getProjectId() !== null;
    }

    /**
     * @param array<string, string> $data
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): FirebaseSendResult
    {
        if (!$this->isEnabled()) {
            return FirebaseSendResult::skipped('Firebase non configuré.');
        }

        $accessToken = $this->accessTokenProvider->getAccessToken();
        $projectId = $this->accessTokenProvider->getProjectId();
        if ($accessToken === null || $projectId === null) {
            return FirebaseSendResult::failed('Impossible d\'obtenir un token Firebase.');
        }

        $message = [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
            'android' => [
                'priority' => 'HIGH',
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        $response = $this->httpClient->request(
            'POST',
            sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['message' => $message],
            ],
        );

        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            return FirebaseSendResult::success();
        }

        $errorBody = $response->getContent(false);
        $invalidToken = str_contains($errorBody, 'NOT_FOUND')
            || str_contains($errorBody, 'UNREGISTERED')
            || str_contains($errorBody, 'INVALID_ARGUMENT');

        return FirebaseSendResult::failed($errorBody, $invalidToken);
    }
}
