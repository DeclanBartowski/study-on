<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class LessonTest extends AbstractTest
{
    public function testLessonList()
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/lessons');
        $this->assertResponseCode(200, $client->getResponse());

        $this->assertCount(9, $crawler->filter('.lesson-card'));
    }

    public function testLessonShow(): void
    {
        $client = self::getClient();

        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);

        $client->request('GET', '/lessons/' . $lesson->getId());

        $response = $client->getResponse();


        $this->assertResponseOk($response);
        $this->assertSelectorTextContains('.h1', $lesson->getName());
    }

    public function testLessonNotFound(): void
    {
        $client = self::getClient();
        $client->request('GET', '/lesson/9999999999999');

        $this->assertResponseNotFound($client->getResponse());
    }

    public function testLessonCreation(): void
    {
        $client = self::getClient();
        $course = static::$em->getRepository(Course::class)->findOneBy([]);
        $crawler = $client->request('GET', '/courses/' . $course->getId());


        $link = $crawler->selectLink('Добавить урок')->link();
        $crawler = $client->click($link);

        $this->assertResponseOk($client->getResponse());
        $this->assertSelectorTextContains('h1', 'Создать новый урок');

        $form = $crawler->selectButton('Сохранить')->form();
        $form['lesson[name]'] = 'Новый урок';
        $form['lesson[content]'] = 'Содержание нового урока';
        $form['lesson[course]'] = $course->getId();
        $form['lesson[orderNumber]'] = 4;

        $client->submit($form);

        $this->assertResponseRedirects('/courses/' . $course->getId());

        $course = static::$em->getRepository(Course::class)->find($course->getId());
        $this->assertEquals(4, $course->getLessons()->count(),
            'Неверное количество уроков, их ' . $course->getLessons()->count());
    }

    public function testLessonCreationValidation(): void
    {
        $client = self::getClient();
        $course = static::$em->getRepository(Course::class)->findOneBy([]);

        $crawler = $client->request('GET', '/lessons/new?course_id=' . $course->getId());

        $form = $crawler->selectButton('Сохранить')->form();
        $form['lesson[name]'] = 'Новый урок';
        $form['lesson[content]'] = 'Содержание нового урока';
        $form['lesson[orderNumber]'] = '111111111111111';

        $client->submit($form);

        $this->assertResponseCode(422, $client->getResponse());
        $this->assertSelectorTextContains('.invalid-feedback', 'Значение должно быть между 0 и 10000.');
    }

    public function testLessonEdit(): void
    {
        $client = self::getClient();
        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);

        $crawler = $client->request('GET', '/lessons/' . $lesson->getId() . '/edit');

        $this->assertInputValueSame('lesson[name]', $lesson->getName());

        $form = $crawler->selectButton('Обновить')->form();
        $form['lesson[name]'] = 'Новое название';

        $client->submit($form);

        $this->assertResponseRedirects('/lessons');

        $updatedLesson = static::$em->getRepository(Lesson::class)->find($lesson->getId());
        $this->assertEquals('Новое название', $updatedLesson->getName());
    }

    public function testLessonDeletion(): void
    {
        $client = self::getClient();
        $lesson = static::$em->getRepository(Lesson::class)->findOneBy([]);
        $crawler = $client->request('GET', '/lessons/' . $lesson->getId() . '/edit');

        $course = $lesson->getCourse();

        $form = $crawler->selectButton('Удалить')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/courses/' . $course->getId());

        static::$em->clear();
        $updatedCourse = static::$em->getRepository(Course::class)->find($course->getId());

        $this->assertEquals(2, $updatedCourse->getLessons()->count());
    }

    /**
     * List of fixtures for certain test
     */
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }
}
