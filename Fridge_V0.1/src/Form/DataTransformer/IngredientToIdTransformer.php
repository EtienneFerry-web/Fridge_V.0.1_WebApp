<?php

namespace App\Form\DataTransformer;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IngredientToIdTransformer implements DataTransformerInterface
{
    public function __construct(private IngredientRepository $repo) {}

    public function transform(mixed $value): string
    {
        if ($value instanceof Ingredient) {
            return (string) $value->getId();
        }
        return '';
    }

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