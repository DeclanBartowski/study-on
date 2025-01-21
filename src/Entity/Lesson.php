<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Course", inversedBy: "lessons")]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Название урока не может быть пустым.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Название урока должно содержать минимум {{ limit }} символа.',
        maxMessage: 'Название урока не может быть длиннее {{ limit }} символов.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: 'Описание урока не может быть пустым.')]
    private ?string $content = null;

    #[ORM\Column(type: "smallint")]
    #[Assert\Range(options: [
        'min' => 0,
        'max' => 10000
    ], notInRangeMessage: 'Значение должно быть между {{ min }} и {{ max }}.')]
    #[Assert\Type(type: 'integer')]
    #[Assert\NotBlank]
    private ?int $orderNumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }
}
