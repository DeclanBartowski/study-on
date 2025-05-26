<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    public const TYPE_FULL = 1;
    public const TYPE_RENT = 2;
    public const TYPE_FREE = 3;

    public const RENT_TYPE_TIME = '1 week';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $symbolCode = null;

    #[ORM\Column(type: 'smallint')]
    private int $type;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Название урока не может быть пустым.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Название курса должно содержать минимум {{ limit }} символа.',
        maxMessage: 'Название курса не может быть длиннее {{ limit }} символов.'
    )]
    private ?string $name;

    #[ORM\Column(type: "text", length: 1000)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: "App\Entity\Lesson", mappedBy: "course", cascade: ['remove'])]
    #[ORM\OrderBy(['orderNumber' => 'ASC'])]
    private Collection $lessons;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbolCode(): ?string
    {
        return $this->symbolCode;
    }

    public function setSymbolCode(string $symbolCode): self
    {
        $this->symbolCode = $symbolCode;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons[] = $lesson;
            $lesson->setCourse($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless the relationship)
            $lesson->setCourse(null);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}
