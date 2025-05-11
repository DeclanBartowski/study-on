<?php

namespace App\Service;

class UserBillingService
{

    public function __construct(protected BillingClient $billingClient)
    {
    }

    public function getUserInfo($user): array
    {
        if (!$user) {
            return [];
        }

        $this->billingClient->setHeaders([
            'Authorization: Bearer ' . $user->getApiToken()
        ]);

        return $this->billingClient->get('/api/v1/users/current');
    }
}
