<?php

namespace App\Tests\Auth;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Tests\AbstractTest;

class AccessTest extends AbstractTest
{
    public function testUnauthorizedAccess(): void
    {
        $client = self::getClient();

        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);

        $client->request('GET', '/courses');
        $this->assertResponseStatusCodeSame(302);

        $client->request('GET', '/courses/' . $course->getId());
        $this->assertResponseStatusCodeSame(302);

        $client->request('GET', '/lessons/' . $lesson->getId());
        $this->assertResponseStatusCodeSame(302);
    }

    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
