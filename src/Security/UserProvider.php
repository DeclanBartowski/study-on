<?php

namespace App\Security;

use App\Service\BillingClient;
use App\Service\TokenService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{

    public function __construct(
        private RequestStack $request,
        private TokenService $tokenService,
        private BillingClient $billingClient
    ) {
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier($identifier): UserInterface
    {
        try {
            $securityToken = $this->request->getSession()->get('_security_main');
            if ($securityToken) {
                $token = unserialize($securityToken);

                if ($token instanceof TokenInterface) {
                    return $token->getUser();
                }
            }

            return $this->request->getSession()->get('user');
        } catch (\Exception $exception) {
            throw new \Exception(sprintf('User with email "%s" not found.', $identifier));
        }
    }

    /**
     * @deprecated since Symfony 5.3, loadUserByIdentifier() is used instead
     */
    public function loadUserByUsername($username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        if ($this->tokenService->isTokenExpired($user->getApiToken())) {
            $response = $this->billingClient->post('/api/v1/token/refresh', [
                'refresh_token' => $user->getRefreshToken(),
            ]);
            if (!$response['refresh_token'] || !$response['token']) {
                throw new UnsupportedUserException('Ошибка при получении токена пользователя');
            }
            $user->setApiToken($response['token']);
            $user->setRefreshToken($response['refresh_token']);
            return $user;
        }

        return $this->loadUserByIdentifier($user->getEmail());
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the hashed password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // TODO: when hashed passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newHashedPassword);
    }
}
