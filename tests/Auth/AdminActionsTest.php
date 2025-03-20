<?php

namespace App\Tests\Auth;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Security\User;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class AdminActionsTest extends AbstractTest
{
    public function testAdminCanEditCourse(): void
    {
        $client = self::getClient();
        $client->disableReboot();

        $client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock('')
        );

        $client->request('GET', '/login');

        $billingClientMock = $client->getContainer()->get('App\Service\BillingClient');

        $response = $billingClientMock->login([
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);

        $this->assertArrayHasKey('token', $response);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles($response['roles']);
        $user->setApiToken($response['token']);

        $client->loginUser($user);

        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $client->request('GET', '/courses/' . $course->getId() . '/edit');
        $this->assertResponseIsSuccessful();
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
