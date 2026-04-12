<?php

namespace App\DataFixtures;

use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LikeRecetteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Tableau : [référence user, référence recette]
        $arrLikes = [
            [UserFixtures::USER_ETIENNE, RecetteFixtures::PATES_TOMATE],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::MOUSSE_CHOCO],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::CREPES],
            [UserFixtures::USER_ALICE,   RecetteFixtures::SOUPE_TOMATES],
            [UserFixtures::USER_ALICE,   RecetteFixtures::CREPES],
            [UserFixtures::USER_BOB,     RecetteFixtures::POULET_ROTI],
            [UserFixtures::USER_BOB,     RecetteFixtures::PATES_TOMATE],
            [UserFixtures::USER_BOB,     RecetteFixtures::MOUSSE_CHOCO],
        ];

        foreach ($arrLikes as [$strUserRef, $strRecetteRef]) {
            $objLike = new LikeRecette();
            $objLike->setLikeDate(new \DateTimeImmutable())
                    ->setLikeUser($this->getReference($strUserRef, User::class))
                    ->setLikeRecette($this->getReference($strRecetteRef, Recette::class));
            $manager->persist($objLike);
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