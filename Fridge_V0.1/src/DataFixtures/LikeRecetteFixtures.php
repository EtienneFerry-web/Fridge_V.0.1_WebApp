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
        $arrLikes = [
            [UserFixtures::USER_ETIENNE, RecetteFixtures::PATES_TOMATE],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::MOUSSE_CHOCO],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::CREPES],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::RISOTTO],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::TIRAMISU],
            [UserFixtures::USER_ETIENNE, RecetteFixtures::FONDANT_CHOCO],
            [UserFixtures::USER_ALICE,   RecetteFixtures::SOUPE_TOMATES],
            [UserFixtures::USER_ALICE,   RecetteFixtures::CREPES],
            [UserFixtures::USER_ALICE,   RecetteFixtures::CURRY_LEGUMES],
            [UserFixtures::USER_ALICE,   RecetteFixtures::VELOUTE_COURGETTE],
            [UserFixtures::USER_ALICE,   RecetteFixtures::RATATOUILLE],
            [UserFixtures::USER_ALICE,   RecetteFixtures::CLAFOUTIS_FRAISE],
            [UserFixtures::USER_BOB,     RecetteFixtures::POULET_ROTI],
            [UserFixtures::USER_BOB,     RecetteFixtures::PATES_TOMATE],
            [UserFixtures::USER_BOB,     RecetteFixtures::MOUSSE_CHOCO],
            [UserFixtures::USER_BOB,     RecetteFixtures::BOLOGNESE],
            [UserFixtures::USER_BOB,     RecetteFixtures::PAD_THAI],
            [UserFixtures::USER_BOB,     RecetteFixtures::RIZ_CANTONAIS],
            [UserFixtures::USER_BOB,     RecetteFixtures::SALADE_CAESAR],
            [UserFixtures::ADMIN,        RecetteFixtures::GATEAU_CHOCO],
            [UserFixtures::ADMIN,        RecetteFixtures::TARTE_CITRON],
            [UserFixtures::MODERATOR,    RecetteFixtures::QUICHE_LORRAINE],
            [UserFixtures::MODERATOR,    RecetteFixtures::GRATIN_DAUPHINOIS],
        ];

        foreach ($arrLikes as [$strUserRef, $strRecetteRef]) {
            $objLike = new LikeRecette();
            $objLike->setLikeDate(new \DateTimeImmutable('-' . rand(1, 60) . ' days'))
                    ->setLikeUser($this->getReference($strUserRef, User::class))
                    ->setLikeRecette($this->getReference($strRecetteRef, Recette::class));
            $manager->persist($objLike);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, RecetteFixtures::class];
    }
}