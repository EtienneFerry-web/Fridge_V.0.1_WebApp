<?php

namespace App\Factory;

use App\Entity\Recette;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Recette>
 */
final class RecetteFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Recette::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'recetteLibelle'      => self::faker()->sentence(3),
            'recetteDescription'  => self::faker()->paragraph(),
            'recetteDifficulte'   => self::faker()->randomElement(['Facile', 'Moyen', 'Difficile']),
            'recettePortion'      => self::faker()->numberBetween(1, 8),
            'recetteTempsPrepa'   => self::faker()->numberBetween(5, 60),
            'recetteTempsCuisson' => self::faker()->numberBetween(0, 120),
            'recetteStatut'       => 'publie', 
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}