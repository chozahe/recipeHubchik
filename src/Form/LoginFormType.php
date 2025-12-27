<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\LoginUserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<LoginUserDto>
 */
class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'example@mail.com',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'email',
                    'name' => '_username',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль',
                'empty_data' => '',
                'mapped' => false,
                'attr' => [
                    'placeholder' => '••••••••',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'current-password',
                    'name' => '_password',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LoginUserDto::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'login_form',
        ]);
    }
}
