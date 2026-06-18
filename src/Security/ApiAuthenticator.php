<?php

namespace App\Security;

use App\Repository\ParentSessionRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ApiAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ParentSessionRepository $parentSessionRepository,
        #[Autowire(env: 'API_TOKEN')]
        private readonly string $apiToken,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->isMethod('OPTIONS')) {
            return false;
        }

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return false;
        }

        if ($request->isMethod('POST') && \in_array($request->getPathInfo(), ['/api/auth/login', '/api/auth/change-pin'], true)) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->extractToken($request);

        if ($token === null || $token === '') {
            throw new CustomUserMessageAuthenticationException('Token d\'authentification manquant.');
        }

        $session = $this->parentSessionRepository->findValidByTokenHash(hash('sha256', $token));
        if ($session !== null) {
            $parent = $session->getParent();

            return new SelfValidatingPassport(
                new UserBadge(
                    (string) $parent->getId(),
                    fn (): ParentUser => new ParentUser(
                        $parent->getId(),
                        $parent->getNumeroTelephoneTuteur(),
                        $parent->getNomTuteur(),
                    ),
                ),
            );
        }

        if ($this->apiToken !== '' && hash_equals($this->apiToken, $token)) {
            return new SelfValidatingPassport(
                new UserBadge('api', static fn (): ApiUser => new ApiUser()),
            );
        }

        throw new CustomUserMessageAuthenticationException('Token d\'authentification invalide.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new JsonResponse(['error' => $message], Response::HTTP_UNAUTHORIZED);
    }

    private function extractToken(Request $request): ?string
    {
        $headerToken = $request->headers->get('X-API-KEY');
        if (is_string($headerToken) && $headerToken !== '') {
            return $headerToken;
        }

        $authorization = $request->headers->get('Authorization', '');
        if (str_starts_with($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return null;
    }
}
