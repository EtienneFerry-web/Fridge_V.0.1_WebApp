<?php

namespace App\Command;

use App\Service\RecetteImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-import-spoonacular',
    description: 'Importe une recette Spoonacular en BDD pour test',
)]
class TestImportSpoonacularCommand extends Command
{
    public function __construct(private RecetteImporter $importer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'spoonacular_id',
            InputArgument::OPTIONAL,
            'ID Spoonacular de la recette à importer (défaut : 715538 = Bruschetta)',
            715538
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io  = new SymfonyStyle($input, $output);
        $intId = (int) $input->getArgument('spoonacular_id');

        $io->title("Import Spoonacular #{$intId}");

        try {
            $objRecette = $this->importer->importFromSpoonacular($intId);

            $io->success(sprintf(
                "Recette importée : ID interne=%d | Spoonacular=%d | Titre=%s",
                $objRecette->getId(),
                $objRecette->getSpoonacularId(),
                $objRecette->getRecetteLibelle()
            ));

            $io->section('Détails');
            $io->definitionList(
                ['Source URL'   => $objRecette->getSourceUrl() ?? '(aucun)'],
                ['Origine'      => $objRecette->getRecetteOrigine() ?? '(non mappée)'],
                ['Portions'     => $objRecette->getRecettePortion() ?? '(non précisé)'],
                ['Temps total'  => ($objRecette->getRecetteTempsCuisson() ?? 0) . ' min'],
                ['Photo'        => $objRecette->getRecettePhoto() ?? '(aucune)'],
                ['Étapes'       => count($objRecette->getEtapes()) . ' étape(s)'],
                ['Ingrédients'  => count($objRecette->getContenirs()) . ' ingrédient(s)'],
                ['Régimes'      => implode(', ', array_map(
                    fn($r) => $r->getRegimeLibelle(),
                    $objRecette->getRegimes()->toArray()
                )) ?: '(aucun)'],
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('Erreur lors de l\'import : ' . $e->getMessage());
            $io->writeln('<comment>Trace :</comment>');
            $io->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}