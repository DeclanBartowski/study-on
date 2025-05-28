<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $courses = [
            [
                'name' => 'Фулстек-разработчик',
                'description' => 'Освоите полный цикл создания сайтов и веб‑приложений — с нуля за 16 месяцев. Будете много практиковаться и получите реальный опыт.',
                'symbolCode' => 'fullstack_developer',
                'type' => Course::TYPE_FULL,
                'price' => 10000,
                'lessons' => [
                    [
                        'name' => 'Первый код',
                        'content' => 'Задачи разработчика, базовые элементы, HTML, CSS',
                        'number' => 1
                    ],
                    [
                        'name' => 'Что такое HTML и CSS',
                        'content' => 'Теги HTML, заголовки, абзац, ссылка, атрибуты, один тег в другом, изображения, структура HTML‑документа, правила CSS, тег style, CSS-файл, блоки, отступы, подпись к обложке, playground',
                        'number' => 2
                    ],
                    [
                        'name' => 'Базовые CSS-свойства',
                        'content' => 'Сборка лендинг, размеры в пикселях, размеры в процентах и долях, цвета в HTML, фон элемента, позиция, размер, повтор фона, прозрачность, коробка в коробке, наследование, типографика, больше вёрстки',
                        'number' => 3
                    ],
                ]
            ],
            [
                'name' => 'Инженер по тестированию',
                'description' => 'Станьте тестировщиком за 5 месяцев с нуля. Будете много практиковаться и получите реальный опыт.',
                'symbolCode' => 'qa-engineer',
                'type' => Course::TYPE_FULL,
                'price' => 20000,
                'lessons' => [
                    [
                        'name' => 'Роль тестировщика в IT‑команде',
                        'content' => 'Узнаете, что такое тестирование, как работает команда и из чего состоит цикл тестирования',
                        'number' => 1
                    ],
                    [
                        'name' => 'Чек-листы и баг‑репорты',
                        'content' => 'Чек-листы тестировщика, что такое баг, баг-репорт и его заголовок, шаги воспроизведения, приоритеты, окружение, логи, скриншоты и скринкасты, кросс-браузерность, оракул',
                        'number' => 2
                    ],
                    [
                        'name' => 'Тест-кейсы',
                        'content' => 'Статусы тест-кейсов и тест-сьюты, отчёт о тестировании, регрессионное и смоук-тестирование, тестирование локализации',
                        'number' => 3
                    ],
                ]
            ],
            [
                'name' => 'React-разработчик',
                'description' => 'Тем, кто знаком с основами JS, CSS, HTML и умеет работать с Git и NPM. Если вы не уверены, что справитесь с курсом, попробуйте пройти бесплатный тест из задач по вёрстке и JavaScript.',
                'symbolCode' => 'react-developer',
                'type' => Course::TYPE_RENT,
                'price' => 10000,
                'lessons' => [
                    [
                        'name' => 'Как всё устроено',
                        'content' => 'Вы узнаете больше о курсе. Ознакомитесь с организационными деталями и работой команды сопровождения, а в конце мы синхронизируемся по ожиданиям.',
                        'number' => 1
                    ],
                    [
                        'name' => 'Тестирование',
                        'content' => 'Чтобы вы проверили знания и объективно оценили свои силы, мы предлагаем ответить на 11 вопросов. Потом поможем интерпретировать результаты и перейти к курсу.',
                        'number' => 2
                    ],
                    [
                        'name' => 'Погружение в React и Redux',
                        'content' => 'В этом модуле вы изучите основы и инструментарий React. Научитесь работать с классовыми и функциональными компонентами и попрактикуетесь в их написании. Узнаете, как с помощью хуков привнести мощь классовых компонентов в функциональные. Создадите простую заготовку React-приложения с помощью CRA и узнаете, как выполнить его отладку с применением плагина React DevTools. Узнаете, что такое «состояние» в терминологии современных фреймворков и библиотек. Научитесь работать с одной из самых популярных библиотек для хранения состояния — Redux.',
                        'number' => 3
                    ],
                ]
            ]
        ];

        foreach ($courses as $courseItem) {
            $course = new Course();
            $course->setSymbolCode($courseItem['symbolCode']);
            $course->setName($courseItem['name']);
            $course->setDescription($courseItem['description']);
            $course->setPrice($courseItem['price']);
            $course->setType($courseItem['type']);
            $manager->persist($course);
            $manager->flush();
            foreach ($courseItem['lessons'] as $lessonItem) {
                $lesson = new Lesson();
                $lesson->setName($lessonItem['name']);
                $lesson->setContent($lessonItem['content']);
                $lesson->setOrderNumber($lessonItem['number']);
                $lesson->setCourse($course);
                $manager->persist($lesson);
                $manager->flush();
            }
        }

        $manager->flush();
    }
}
