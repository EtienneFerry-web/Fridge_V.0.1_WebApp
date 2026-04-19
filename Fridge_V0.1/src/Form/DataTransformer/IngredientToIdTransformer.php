<?php

namespace App\Form\DataTransformer;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer convertissant une entité Ingredient en son id (string) et inversement.
 *
 * Utilisé par ContenirType pour gérer le champ ingredient caché (HiddenType) :
 * le formulaire envoie un id numérique, le transformer le résout en objet Ingredient.
 */
class IngredientToIdTransformer implements DataTransformerInterface
{
    public function __construct(private IngredientRepository $repo) {}

    /**
     * Convertit un objet Ingredient en son id sous forme de chaîne (modèle → vue).
     */
    public function transform(mixed $value): string
    {
        if ($value instanceof Ingredient) {
            return (string) $value->getId();
        }
        return '';
    }

    /**
     * Convertit un id (string) en entité Ingredient (vue → modèle).
     *
     * @throws TransformationFailedException si aucun ingrédient ne correspond à l'id reçu
     */
    public function reverseTransform(mixed $value): ?Ingredient
    {
        if (!$value) {
            return null;
        }

        $ingredient = $this->repo->find((int) $value);

        if (!$ingredient) {
            throw new TransformationFailedException(
                sprintf('Ingrédient avec l\'id "%s" introuvable.', $value)
            );
        }

        return $ingredient;
    }
}