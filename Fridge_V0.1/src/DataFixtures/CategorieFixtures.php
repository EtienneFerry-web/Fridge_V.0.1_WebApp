<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    const ENTREE          = 'categorie-entree';
    const PLAT_PRINCIPAL  = 'categorie-plat-principal';
    const DESSERT         = 'categorie-dessert';
    const PETIT_DEJEUNER  = 'categorie-petit-dejeuner';
    const SNACK           = 'categorie-snack';

    public function load(ObjectManager $manager): void
    {
        $arrCategories = [
            self::ENTREE         => 'Entrée',
            self::PLAT_PRINCIPAL => 'Plat principal',
            self::DESSERT        => 'Dessert',
            self::PETIT_DEJEUNER => 'Petit-déjeuner',
            self::SNACK          => 'Snack',
        ];

        foreach ($arrCategories as $strReference => $strLibelle) {
            $objCategorie = new Categorie();
            $objCategorie->setCategorieLibelle($strLibelle);
            $manager->persist($objCategorie);

            $this->addReference($strReference, $objCategorie);
        }

        $manager->flush();
    }
}