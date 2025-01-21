<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;

class CourseTest extends AbstractTest
{

    public function testCourseList()
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses');
        $this->assertResponseCode(200, $client->getResponse());

        $this->assertCount(3, $crawler->filter('.course-card'));
    }

    public function testCourseShow(): void
    {
        $client = self::getClient();

        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $client->request('GET', '/courses/' . $course->getId());

        $response = $client->getResponse();

        $this->assertResponseOk($response);
        $this->assertSelectorTextContains('.h1', $course->getName());
    }

    public function testCourseNotFound(): void
    {
        $client = self::getClient();
        $client->request('GET', '/course/9999999999999');

        $this->assertResponseNotFound($client->getResponse());
    }

    public function testCourseCreation(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Добавить новый курс')->link();
        $crawler = $client->click($link);

        $this->assertResponseOk($client->getResponse());
        $this->assertSelectorTextContains('h1', 'Добавить новый курс');

        $form = $crawler->selectButton('Сохранить')->form();
        $form['course[name]'] = 'Новый курс';
        $form['course[description]'] = 'Содержание нового курса';

        $client->submit($form);

        $this->assertResponseRedirects('/courses');

        $courses = static::$em->getRepository(Course::class)->findAll();
        $this->assertEquals(4, count($courses),
            'Неверное количество курсов, их ' . count($courses));
    }

    public function testCourseCreationValidation(): void
    {
        $client = self::getClient();
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
