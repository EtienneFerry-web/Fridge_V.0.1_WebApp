<?php

namespace App\DataFixtures;

use App\Entity\Regime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RegimeFixtures extends Fixture
{
    // Constantes pour les références — utilisées par les autres fixtures
    const OMNIVORE    = 'regime-omnivore';
    const VEGETARIEN  = 'regime-vegetarien';
    const VEGAN       = 'regime-vegan';
    const SANS_GLUTEN = 'regime-sans-gluten';
    const SANS_LACTOSE = 'regime-sans-lactose';

    public function load(ObjectManager $manager): void
    {
        $arrRegimes = [
            self::OMNIVORE     => 'Omnivore',
            self::VEGETARIEN   => 'Végétarien',
            self::VEGAN        => 'Vegan',
            self::SANS_GLUTEN  => 'Sans gluten',
            self::SANS_LACTOSE => 'Sans lactose',
        ];

        foreach ($arrRegimes as $strReference => $strLibelle) {
            $objRegime = new Regime();
            $objRegime->setRegimeLibelle($strLibelle);
            $manager->persist($objRegime);

            // On sauvegarde l'objet pour pouvoir le réutiliser ailleurs
            $this->addReference($strReference, $objRegime);
        }

        $manager->flush();
    }
}