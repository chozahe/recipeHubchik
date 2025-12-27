<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\CreateReviewDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CreateReviewDto>
 */
final class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Рейтинг',
                'choices' => [
                    '1 - Очень плохо' => 1,
                    '2 - Плохо' => 2,
                    '3 - Нормально' => 3,
                    '4 - Хорошо' => 4,
                    '5 - Отлично' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'mt-1',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Комментарий',
                'required' => false,
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white',
                    'rows' => 5,
                    'maxlength' => 1000,
                    'placeholder' => 'Расскажите о вашем впечатлении от рецепта...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateReviewDto::class,
        ]);
    }
}
