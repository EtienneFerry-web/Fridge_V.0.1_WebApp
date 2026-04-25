<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

/**
 * Fournit l'accès aux utilisateurs système de l'application.
 *
 * Les utilisateurs système (comme Spoonacular) servent d'auteurs techniques
 * pour les contenus importés depuis des sources externes. Ils ne doivent
 * jamais être utilisés pour authentifier une session réelle.
 */
class SystemUserProvider
{
    public const SPOONACULAR_EMAIL = 'spoonacular@system.local';

    public function __construct(
        private UserRepository $objUserRepository,
    ) {}

    /**
     * Retourne le user système Spoonacular utilisé comme auteur des recettes importées.
     *
     * @throws \RuntimeException Si le user système n'existe pas en BDD
     *                           (les fixtures n'ont probablement pas été chargées).
     */
    public function getSpoonacularUser(): User
    {
        $objUser = $this->objUserRepository->findOneBy(['strEmail' => self::SPOONACULAR_EMAIL]);

        if (!$objUser instanceof User) {
            throw new \RuntimeException(
                'Le user système Spoonacular est introuvable. ' .
                'Lance "php bin/console doctrine:fixtures:load" pour le créer.'
            );
        }

        return $objUser;
    }
}