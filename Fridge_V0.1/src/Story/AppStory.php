<?php

namespace App\Story;

use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

/**
 * Story principale des fixtures Foundry.
 *
 * Point d'entrée pour la génération des données de test et de démonstration.
 * À compléter avec les factories (UserFactory, RecetteFactory, etc.) au fur et à mesure du développement.
 */
#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    /**
     * Construit les données de fixture. À alimenter avec les factories Foundry.
     */
    public function build(): void
    {
        // SomeFactory::createOne();
    }
}
