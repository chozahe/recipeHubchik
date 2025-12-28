<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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
        #[Autowire(service: 'monolog.logger.security')]
        private readonly LoggerInterface $logger,
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

        $this->logger->info('Login attempt', [
            'email' => $email,
            'ip' => $request->getClientIp(),
        ]);

        return new Passport(
            new UserBadge($email, function (string $userIdentifier) use ($request): User {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                if (null === $user) {
                    $this->logger->warning('Login failed: user not found', [
                        'email' => $userIdentifier,
                        'ip' => $request->getClientIp(),
                    ]);

                    throw new CustomUserMessageAuthenticationException('Неверный email или пароль');
                }

                if (!$user->isActive()) {
                    $this->logger->warning('Login blocked: user banned', [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'ip' => $request->getClientIp(),
                    ]);

                    throw new CustomUserMessageAuthenticationException('Ваш аккаунт заблокирован. Обратитесь к администратору.');
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', is_string($csrfToken) ? $csrfToken : null),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if ($user instanceof User) {
            $this->logger->info('Login successful', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'ip' => $request->getClientIp(),
            ]);
        }

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
        $this->logger->warning('Login failed: invalid credentials', [
            'email' => $request->request->get('_username'),
            'ip' => $request->getClientIp(),
        ]);

        $request->getSession()->set('_security.last_error', $exception);

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login'),
            Response::HTTP_SEE_OTHER
        );
    }
}
