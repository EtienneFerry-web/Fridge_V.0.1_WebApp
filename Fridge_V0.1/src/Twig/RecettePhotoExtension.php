<?php

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filtre Twig pour résoudre l'URL d'une photo de recette de manière transparente.
 *
 * Comportement :
 * - URL externe (Spoonacular, Unsplash, etc.) : retournée telle quelle
 * - Nom de fichier local : préfixée avec asset('uploads/recettes/...')
 * - null/vide : image par défaut
 *
 * Usage Twig : {{ recette.recettePhoto|recette_photo }}
 */
class RecettePhotoExtension extends AbstractExtension
{
    private const DEFAULT_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1200';

    public function __construct(private Packages $assetPackages) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('recette_photo', [$this, 'resolvePhotoUrl']),
        ];
    }

    public function resolvePhotoUrl(?string $strPhoto): string
    {
        if ($strPhoto === null || $strPhoto === '') {
            return self::DEFAULT_IMAGE;
        }

        // URL externe : on la retourne telle quelle
        if (str_starts_with($strPhoto, 'http://')
            || str_starts_with($strPhoto, 'https://')
            || str_starts_with($strPhoto, '//')) {
            return $strPhoto;
        }

        // Sinon, fichier local dans /public/uploads/recettes/
        return $this->assetPackages->getUrl('uploads/recettes/' . $strPhoto);
    }
}