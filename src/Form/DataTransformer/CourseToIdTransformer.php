<?php

namespace App\Form\DataTransformer;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class CourseToIdTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value): ?int
    {
        return $value?->getId();
    }

    public function reverseTransform($value): ?Course
    {
        if (!$value) {
            return null;
        }

        return $this->entityManager->getRepository(Course::class)->find($value);
    }
}
