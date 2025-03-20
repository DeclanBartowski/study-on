<?php

namespace App\Tests\Auth;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Security\User;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class UserActionsTest extends AbstractTest
{
    public function testUserCannotEditCourse(): void
    {
        $client = self::getClient();
        $course = static::$em->getRepository(Course::class)->findOneBy([]);

        $client->disableReboot();

        $client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock('')
        );

        $client->request('GET', '/login');

        $billingClientMock = $client->getContainer()->get('App\Service\BillingClient');

        $response = $billingClientMock->login([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertArrayHasKey('token', $response);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles($response['roles']);
        $user->setApiToken($response['token']);

        $client->loginUser($user);

        $client->request('GET', '/courses/' . $course->getId() . '/edit');

        $this->assertResponseStatusCodeSame(403);
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
