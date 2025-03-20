<?php

namespace App\Tests\Auth;

use App\DataFixtures\CourseFixtures;
use App\Entity\Lesson;
use App\Security\User;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class LessonAccessTest extends AbstractTest
{
    public function testAuthorizedAccess(): void
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
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertArrayHasKey('token', $response);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles($response['roles']);
        $user->setApiToken($response['token']);

        $client->loginUser($user);

        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);
        $client->request('GET', '/lessons/' . $lesson->getId());
        $this->assertResponseIsSuccessful();
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
