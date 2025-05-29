<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Service\BillingClient;
use App\Service\TransactionService;

class CourseTest extends AbstractTest
{

    public function testCourseNotFound(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getCasualUser());
        $client->request('GET', '/course/9999999999999');

        $this->assertResponseNotFound($client->getResponse());
    }

    public function testCourseCreationValidation(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
        $crawler = $client->request('GET', '/courses/new');

        $form = $crawler->selectButton('Сохранить')->form();
        $form['course[name]'] = 'Но';
        $form['course[description]'] = 'Содержание нового курса';

        $client->submit($form);

        $this->assertResponseCode(422, $client->getResponse());
        $this->assertSelectorTextContains('.invalid-feedback', 'Название курса должно содержать минимум 3 символа.');
    }

    public function testCourseEdit(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $crawler = $client->request('GET', '/courses/' . $course->getId() . '/edit');

        $this->assertInputValueSame('course[name]', $course->getName());
        $form = $crawler->selectButton('Сохранить')->form();
        $form['course[name]'] = 'Новое название';

        $client->submit($form);

        $this->assertResponseRedirects('/courses');

        static::$em->clear();
        $updatedCourse = static::$em->getRepository(Course::class)->find($course->getId());
        $this->assertEquals('Новое название', $updatedCourse->getName());
    }

    public function testCourseDeletion(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $crawler = $client->request('GET', '/courses/' . $course->getId() . '/edit');

        $form = $crawler->selectButton('Удалить')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/courses');

        static::$em->clear();
        $courses = static::$em->getRepository(Course::class)->findAll();

        $this->assertEquals(2, count($courses));
    }

    /**
     * List of fixtures for certain test
     */
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
