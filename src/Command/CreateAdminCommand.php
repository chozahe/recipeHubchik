<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function assert;
use function filter_var;
use function is_string;
use function mb_strlen;
use function preg_match;
use function sprintf;
use function trim;

use const FILTER_VALIDATE_EMAIL;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Создать нового администратора',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Создание администратора RecipeHub');

        // Запрос email
        $email = $io->ask('Email администратора', null, static function (?string $value): string {
            if (null === $value || '' === trim($value)) {
                throw new RuntimeException('Email не может быть пустым');
            }

            if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Email должен быть корректным');
            }

            return trim($value);
        });

        assert(is_string($email));

        // Проверка уникальности email
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null !== $existingUser) {
            $io->error(sprintf('Пользователь с email "%s" уже существует', $email));

            return Command::FAILURE;
        }

        // Запрос имени
        $name = $io->ask('Имя администратора', null, static function (?string $value): string {
            if (null === $value || '' === trim($value)) {
                throw new RuntimeException('Имя не может быть пустым');
            }

            if (mb_strlen(trim($value)) < 2) {
                throw new RuntimeException('Имя должно быть не менее 2 символов');
            }

            if (mb_strlen(trim($value)) > 30) {
                throw new RuntimeException('Имя должно быть не более 30 символов');
            }

            return trim($value);
        });

        assert(is_string($name));

        // Запрос пароля
        $questionHelper = $this->getHelper('question');
        assert($questionHelper instanceof QuestionHelper);
        $passwordQuestion = new Question('Пароль (минимум 8 символов, буквы и цифры): ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(static function (?string $value): string {
            if (null === $value || '' === trim($value)) {
                throw new RuntimeException('Пароль не может быть пустым');
            }

            if (mb_strlen($value) < 8) {
                throw new RuntimeException('Пароль должен быть не менее 8 символов');
            }

            if (1 !== preg_match('/^(?=.*[a-zA-Zа-яёА-ЯЁ])(?=.*\d)/u', $value)) {
                throw new RuntimeException('Пароль должен содержать буквы и цифры');
            }

            return $value;
        });

        $password = $questionHelper->ask($input, $output, $passwordQuestion);
        assert(is_string($password));

        // Подтверждение пароля
        $confirmPasswordQuestion = new Question('Подтвердите пароль: ');
        $confirmPasswordQuestion->setHidden(true);
        $confirmPasswordQuestion->setHiddenFallback(false);

        $confirmPassword = $questionHelper->ask($input, $output, $confirmPasswordQuestion);
        assert(is_string($confirmPassword));

        if ($password !== $confirmPassword) {
            $io->error('Пароли не совпадают');

            return Command::FAILURE;
        }

        // Создание пользователя
        $user = new User(
            email: $email,
            name: $name,
            password: '',
            isAdmin: true,
        );

        // Хеширование пароля
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Сохранение в БД
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Администратор "%s" (%s) успешно создан!',
            $name,
            $email
        ));

        return Command::SUCCESS;
    }
}
