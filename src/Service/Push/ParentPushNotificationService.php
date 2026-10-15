<?php

namespace App\Service\Push;

use App\Entity\Annonce;
use App\Entity\Devoir;
use App\Repository\AnnonceRepository;
use App\Repository\DevoirRepository;
use App\Repository\ParentPushDeviceRepository;
use Psr\Log\LoggerInterface;

final class ParentPushNotificationService
{
    public function __construct(
        private readonly AnnonceRepository $annonceRepository,
        private readonly DevoirRepository $devoirRepository,
        private readonly ParentPushDeviceRepository $pushDeviceRepository,
        private readonly FirebaseMessagingClient $messagingClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function notifyNewAnnonce(Annonce $annonce): void
    {
        if ($annonce->getId() === null) {
            return;
        }

        $parentIds = $this->annonceRepository->findRecipientParentIds($annonce);
        $body = $this->excerpt($annonce->getContenu(), 180);
        $title = sprintf('%s — %s', $annonce->getType(), $annonce->getTitre());

        $this->sendToParentIds($parentIds, $title, $body, [
            'type' => 'annonce',
            'annonceId' => (string) $annonce->getId(),
            'annonceType' => $annonce->getType(),
        ]);
    }

    public function notifyNewDevoir(Devoir $devoir): void
    {
        if ($devoir->getId() === null) {
            return;
        }

        $parentIds = $this->devoirRepository->findRecipientParentIdsForClasse($devoir->getClasseId());
        $body = $devoir->getDescription() !== null && $devoir->getDescription() !== ''
            ? $this->excerpt($devoir->getDescription(), 180)
            : 'Un nouveau devoir est disponible pour la classe.';

        $this->sendToParentIds($parentIds, 'Nouveau devoir — '.$devoir->getTitre(), $body, [
            'type' => 'devoir',
            'devoirId' => (string) $devoir->getId(),
            'classeId' => (string) $devoir->getClasseId(),
        ]);
    }

    /**
     * @param int[] $parentIds
     * @param array<string, string> $data
     */
    private function sendToParentIds(array $parentIds, string $title, string $body, array $data): void
    {
        if (!$this->messagingClient->isEnabled()) {
            $this->logger->info('Push ignoré : Firebase non configuré.', ['data' => $data]);

            return;
        }

        $tokens = $this->pushDeviceRepository->findDeviceTokensForParentIds($parentIds);
        if ($tokens === []) {
            $this->logger->info('Push ignoré : aucun appareil enregistré.', [
                'parentCount' => \count($parentIds),
                'data' => $data,
            ]);

            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $result = $this->messagingClient->sendToDevice($token, $title, $body, $data);
            if ($result->isSuccess()) {
                ++$sent;
                continue;
            }

            ++$failed;
            if ($result->isInvalidToken()) {
                $this->pushDeviceRepository->removeByToken($token);
            }

            $this->logger->warning('Échec envoi push FCM.', [
                'token' => substr($token, 0, 12).'…',
                'error' => $result->getError(),
            ]);
        }

        $this->logger->info('Campagne push terminée.', [
            'sent' => $sent,
            'failed' => $failed,
            'devices' => \count($tokens),
            'data' => $data,
        ]);
    }

    private function excerpt(string $text, int $maxLength): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        if ($normalized === '') {
            return '';
        }

        if (mb_strlen($normalized) <= $maxLength) {
            return $normalized;
        }

        return mb_substr($normalized, 0, $maxLength - 1).'…';
    }
}
