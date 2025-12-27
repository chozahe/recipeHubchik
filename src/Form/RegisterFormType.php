<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\RegisterUserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RegisterUserDto>
 */
class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'example@mail.com',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'email',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Ваше имя',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Иван Петров',
                    'class' => 'w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition',
                    'autocomplete' => 'name',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-2',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль',
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
            ->add('passwordConfirm', PasswordType::class, [
                'label' => 'Подтвердите пароль',
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
            ->addEventListener(FormEvents::POST_SUBMIT, $this->validatePasswordMatch(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegisterUserDto::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'register_form',
        ]);
    }

    private function validatePasswordMatch(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!$form->has('passwordConfirm')) {
            return;
        }

        if (!$data instanceof RegisterUserDto) {
            return;
        }

        $passwordConfirm = $form->get('passwordConfirm')->getData();

        if ($data->password !== $passwordConfirm) {
            $form->get('passwordConfirm')->addError(
                new \Symfony\Component\Form\FormError('Пароли не совпадают')
            );
        }
    }
}
