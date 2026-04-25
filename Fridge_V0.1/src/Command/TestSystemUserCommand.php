<?php

namespace App\Command;

use App\Service\SystemUserProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-system-user',
    description: 'Vérifie l\'existence du user système Spoonacular',
)]
class TestSystemUserCommand extends Command
{
    public function __construct(private SystemUserProvider $provider)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->provider->getSpoonacularUser();
            $io->success(sprintf(
                'User trouvé : ID=%d | Email=%s | Pseudo=%s | Rôles=%s',
                $user->getId(),
                $user->getStrEmail(),
                $user->getStrUsername(),
                implode(', ', $user->getRoles())
            ));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}