<?php

namespace App\Command;

use App\Service\SpoonacularClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-spoonacular',
    description: 'Teste la connexion à l\'API Spoonacular',
)]
class TestSpoonacularCommand extends Command
{
    public function __construct(private SpoonacularClient $client)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Test Spoonacular API');

        try {
            $ingredients = ['chicken', 'onion', 'garlic'];
            $io->section('Recherche avec : ' . implode(', ', $ingredients));

            $recipes = $this->client->findRecipesByIngredients($ingredients, 5);

            if (empty($recipes)) {
                $io->warning('Aucune recette trouvée.');
                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($recipes as $recipe) {
                $rows[] = [
                    $recipe['id'],
                    $recipe['title'],
                    $recipe['usedIngredientCount'],
                    $recipe['missedIngredientCount'],
                ];
            }

            $io->table(
                ['ID', 'Titre', 'Ingr. utilisés', 'Ingr. manquants'],
                $rows
            );

            $io->success(sprintf('%d recettes trouvées !', count($recipes)));
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}