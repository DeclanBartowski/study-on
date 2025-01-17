<?php

namespace App\Form;

use App\Entity\Course;
use App\Form\DataTransformer\TextTranslitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    private TextTranslitTransformer $transformer;

    public function __construct(TextTranslitTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название курса',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание курса',
            ])
            ->add('symbolCode', HiddenType::class);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            $transliteratedName = $this->transformer->transform($data->getName());
            $data->setSymbolCode($transliteratedName);

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
