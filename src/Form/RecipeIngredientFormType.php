<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\RecipeIngredientDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RecipeIngredientDto>
 */
class RecipeIngredientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredientName', TextType::class, [
                'label' => 'Ингредиент',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white',
                    'placeholder' => 'Введите название...',
                    'list' => 'ingredients-datalist',
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Количество',
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white',
                    'step' => '0.1',
                ],
            ])
            ->add('unit', ChoiceType::class, [
                'label' => 'Единица',
                'choices' => [
                    'г' => 'г',
                    'кг' => 'кг',
                    'мл' => 'мл',
                    'л' => 'л',
                    'ст.л.' => 'ст.л.',
                    'ч.л.' => 'ч.л.',
                    'шт.' => 'шт.',
                    'по вкусу' => 'по вкусу',
                ],
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeIngredientDto::class,
        ]);
    }
}
