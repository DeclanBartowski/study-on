<?php

namespace App\Tests\Auth;

use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class RegistrationTest extends AbstractTest
{
    public function testRegistration(): void
    {
        $client = self::getClient();
        $client->disableReboot();

        $client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock('')
        );

        $billingClientMock = $client->getContainer()->get('App\Service\BillingClient');

        $client->request('GET', '/register');

        $response = $billingClientMock->register([
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);
        $this->assertArrayHasKey('token', $response);
    }
}
