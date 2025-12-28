<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ChangePasswordDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ChangePasswordDto>
 */
final class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Текущий пароль',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => '••••••••',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'current-password',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Новый пароль',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => '••••••••',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'new-password',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Подтвердите новый пароль',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => '••••••••',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'new-password',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangePasswordDto::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'change_password',
        ]);
    }
}
