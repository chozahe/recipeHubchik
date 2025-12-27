<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use function is_string;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function supports(Request $request): ?bool
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = (string) $request->request->get('_username', '');
        $password = (string) $request->request->get('_password', '');
        $csrfToken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($email, fn (string $userIdentifier) => $this->userRepository->findOneBy(['email' => $userIdentifier])),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', is_string($csrfToken) ? $csrfToken : null),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if (null !== $targetPath) {
            return new RedirectResponse($targetPath, Response::HTTP_SEE_OTHER);
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('app_home'),
            Response::HTTP_SEE_OTHER
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('_security.last_error', $exception);

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login'),
            Response::HTTP_SEE_OTHER
        );
    }
}
