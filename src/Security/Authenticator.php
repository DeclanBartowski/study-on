<?php

namespace App\Security;

use App\Service\BillingClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private BillingClient $billingClient,
        private Security $security,
    ) {
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // Проверка пароля будет выполнена в API
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        if (!$token->getUser()) {
            throw new CustomUserMessageAuthenticationException('Пользователь не найден.');
        }
        $request->getSession()->set('user', $token->getUser());

        return new RedirectResponse($this->urlGenerator->generate('app_course_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $credentials = $this->getCredentials($request);

        try {
            $response = $this->billingClient->post('/api/v1/auth', [
                'username' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

            if (!isset($response['token']) && !isset($response['user']['roles'])) {
                throw new CustomUserMessageAuthenticationException('Пользователь не найден.');
            }
            $email = $credentials['email'];
            $apiToken = $response['token'];
            $user = new User();
            $user->setApiToken($apiToken);
            $user->setRoles($response['user']['roles']);
            $user->setEmail($email);

            return new SelfValidatingPassport(new UserBadge($email, function ($email) use ($user) {
                return $user;
            }));
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Ошибка при аутентификации: ' . $e->getMessage());
        }
    }
}
