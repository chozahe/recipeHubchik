<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

#[AsCommand(
    name: 'app:user:amnesty-everyone',
    description: 'Разбанить всех заблокированных пользователей',
)]
class AmnestyUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Разбан всех пользователей RecipeHub');

        // Находим всех забаненных пользователей
        $bannedUsers = $this->entityManager->getRepository(User::class)->findBy(['isActive' => false]);

        $bannedCount = count($bannedUsers);

        if (0 === $bannedCount) {
            $io->info('Нет заблокированных пользователей');

            return Command::SUCCESS;
        }

        // Подтверждение операции
        $confirmed = $io->confirm(
            sprintf('Найдено заблокированных пользователей: %d. Разбанить всех?', $bannedCount),
            false
        );

        if (!$confirmed) {
            $io->warning('Операция отменена');

            return Command::SUCCESS;
        }

        // Разбаниваем всех пользователей
        foreach ($bannedUsers as $user) {
            $user->setIsActive(true);
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Успешно разбанено пользователей: %d',
            $bannedCount
        ));

        // Выводим список разбаненных пользователей
        $io->section('Разбаненные пользователи:');
        $rows = [];
        foreach ($bannedUsers as $user) {
            $rows[] = [
                $user->getId(),
                $user->getEmail(),
                $user->getName(),
            ];
        }

        $io->table(
            ['ID', 'Email', 'Имя'],
            $rows
        );

        return Command::SUCCESS;
    }
}
