<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IngredientFixtures extends Fixture
{
    const FARINE        = 'ingredient-farine';
    const OEUFS         = 'ingredient-oeufs';
    const LAIT          = 'ingredient-lait';
    const BEURRE        = 'ingredient-beurre';
    const SUCRE         = 'ingredient-sucre';
    const SEL           = 'ingredient-sel';
    const TOMATES       = 'ingredient-tomates';
    const OIGNONS       = 'ingredient-oignons';
    const AIL           = 'ingredient-ail';
    const POULET        = 'ingredient-poulet';
    const PATES         = 'ingredient-pates';
    const HUILE_OLIVE   = 'ingredient-huile-olive';
    const CHOCOLAT_NOIR = 'ingredient-chocolat-noir';
    const CREME_FRAICHE = 'ingredient-creme-fraiche';
    const BASILIC       = 'ingredient-basilic';

    public function load(ObjectManager $manager): void
    {
        $arrIngredients = [
            self::FARINE        => ['Farine',          'Épicerie'],
            self::OEUFS         => ['Oeufs',            'Produits frais'],
            self::LAIT          => ['Lait',             'Produits laitiers'],
            self::BEURRE        => ['Beurre',           'Produits laitiers'],
            self::SUCRE         => ['Sucre',            'Épicerie'],
            self::SEL           => ['Sel',              'Épicerie'],
            self::TOMATES       => ['Tomates',          'Légumes'],
            self::OIGNONS       => ['Oignons',          'Légumes'],
            self::AIL           => ['Ail',              'Légumes'],
            self::POULET        => ['Poulet',           'Viandes'],
            self::PATES         => ['Pâtes',            'Épicerie'],
            self::HUILE_OLIVE   => ['Huile d\'olive',   'Épicerie'],
            self::CHOCOLAT_NOIR => ['Chocolat noir',    'Épicerie'],
            self::CREME_FRAICHE => ['Crème fraîche',    'Produits laitiers'],
            self::BASILIC       => ['Basilic',          'Herbes'],
        ];

        foreach ($arrIngredients as $strReference => [$strLibelle, $strType]) {
            $objIngredient = new Ingredient();
            $objIngredient->setIngredientLibelle($strLibelle);
            $objIngredient->setIngredientType($strType);
            $manager->persist($objIngredient);

            $this->addReference($strReference, $objIngredient);
        }

        $manager->flush();
    }
}