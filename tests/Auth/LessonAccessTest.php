<?php

namespace App\Tests\Auth;

use App\DataFixtures\CourseFixtures;
use App\Entity\Lesson;
use App\Security\User;
use App\Service\TransactionService;
use App\Tests\AbstractTest;

class LessonAccessTest extends AbstractTest
{
    public function testAuthorizedAccess(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
        $client->disableReboot();

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

        $transactionServiceMock = $this->createMock(TransactionService::class);

        $transactionServiceMock->method('getUserTransactions')
            ->willReturn([
                [
                    "id" => 41,
                    "created_at" => "2025-05-26T14:38:43+00:00",
                    "type" => 1,
                    "amount" => "10000.00",
                    'course_code' => 'fullstack_developer'
                ],
                [
                    "id" => 42,
                    "created_at" => "2025-05-26T14:38:43+00:00",
                    "type" => 1,
                    "amount" => "10000.00",
                    'course_code' => 'qa-engineer'
                ],
                [
                    "id" => 43,
                    "created_at" => "2025-05-26T14:38:43+00:00",
                    "type" => 1,
                    "amount" => "10000.00",
                    'course_code' => 'react-developer'
                ]
            ]);

        $transactionServiceMock->method('isCoursePay')
            ->willReturn(true);

        $transactionServiceMock->method('isCourseBuyed')
            ->willReturn(true);

        $client->getContainer()->set('App\Service\TransactionService', $transactionServiceMock);

        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);
        $client->request('GET', '/lessons/' . $lesson->getId());
        $this->assertResponseIsSuccessful();
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
