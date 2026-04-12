<?php

namespace App\DataFixtures;

use App\Entity\Favori;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FavoriFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Tableau : [référence user, référence recette]
        $arrFavoris = [
            [UserFixtures::USER_ETIENNE, RecetteFixtures::PATES_TOMATE],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::POULET_ROTI],
            [UserFixtures::USER_ALICE,   RecetteFixtures::MOUSSE_CHOCO],
            [UserFixtures::USER_ALICE,   RecetteFixtures::CREPES],
            [UserFixtures::USER_BOB,     RecetteFixtures::SOUPE_TOMATES],
        ];

        foreach ($arrFavoris as [$strUserRef, $strRecetteRef]) {
            $objFavori = new Favori();
            $objFavori->setFavoriDate(new \DateTimeImmutable())
                      ->setFavoriUser($this->getReference($strUserRef, User::class))
                      ->setFavoriRecette($this->getReference($strRecetteRef, Recette::class));
            $manager->persist($objFavori);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            RecetteFixtures::class,
        ];
    }
}