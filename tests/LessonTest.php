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
        $client->loginUser($this->getCasualUser());
        $crawler = $client->request('GET', '/lessons');
        $this->assertResponseCode(200, $client->getResponse());

        $this->assertCount(9, $crawler->filter('.lesson-card'));
    }

    public function testLessonNotFound(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
        $client->request('GET', '/lesson/9999999999999');

        $this->assertResponseNotFound($client->getResponse());
    }

    public function testLessonCreationValidation(): void
    {
        $client = self::getClient();
        $client->loginUser($this->getAdmin());
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
        $client->loginUser($this->getAdmin());

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
        $client->loginUser($this->getAdmin());
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
