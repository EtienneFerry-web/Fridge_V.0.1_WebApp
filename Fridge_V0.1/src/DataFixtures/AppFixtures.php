<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Etape;
use App\Entity\Ingredient;
use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\Regime;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $objHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // =====================
        // REGIMES
        // =====================
        $arrRegimes = [];
        foreach (['Omnivore', 'Végétarien', 'Vegan', 'Sans gluten', 'Sans lactose'] as $strLibelle) {
            $objRegime = new Regime();
            $objRegime->setRegimeLibelle($strLibelle);
            $manager->persist($objRegime);
            $arrRegimes[] = $objRegime;
        }

        // =====================
        // CATEGORIES
        // =====================
        $arrCategories = [];
        foreach (['Entrée', 'Plat principal', 'Dessert', 'Petit-déjeuner', 'Snack'] as $strLibelle) {
            $objCategorie = new Categorie();
            $objCategorie->setCategorieLibelle($strLibelle);
            $manager->persist($objCategorie);
            $arrCategories[] = $objCategorie;
        }

        // =====================
        // INGREDIENTS
        // =====================
        $arrIngredients = [];
        foreach ([
            ['Farine', 'Épicerie'],
            ['Oeufs', 'Produits frais'],
            ['Lait', 'Produits laitiers'],
            ['Beurre', 'Produits laitiers'],
            ['Sucre', 'Épicerie'],
            ['Sel', 'Épicerie'],
            ['Tomates', 'Légumes'],
            ['Oignons', 'Légumes'],
            ['Ail', 'Légumes'],
            ['Poulet', 'Viandes'],
            ['Pâtes', 'Épicerie'],
            ['Huile d\'olive', 'Épicerie'],
            ['Chocolat noir', 'Épicerie'],
            ['Crème fraîche', 'Produits laitiers'],
            ['Basilic', 'Herbes'],
        ] as [$strLibelle, $strType]) {
            $objIngredient = new Ingredient();
            $objIngredient->setIngredientLibelle($strLibelle);
            $objIngredient->setIngredientType($strType);
            $manager->persist($objIngredient);
            $arrIngredients[$strLibelle] = $objIngredient;
        }

        // =====================
        // USERS
        // =====================

        // Admin
        $objAdmin = new User();
        $objAdmin->setStrName('Admin');
        $objAdmin->setStrFirstname('Super');
        $objAdmin->setStrUsername('admin_fridge');
        $objAdmin->setStrEmail('admin@fridge.fr');
        $objAdmin->setRoles(['ROLE_ADMIN']);
        $objAdmin->setPassword($this->objHasher->hashPassword($objAdmin, 'Admin1234!'));
        $objAdmin->setIsVerified(true);
        $objAdmin->addRegime($arrRegimes[0]);
        $manager->persist($objAdmin);

        // User test
        $objUser = new User();
        $objUser->setStrName('Ferry');
        $objUser->setStrFirstname('Etienne');
        $objUser->setStrUsername('etienne_ferry');
        $objUser->setStrEmail('etienne@fridge.fr');
        $objUser->setRoles(['ROLE_USER']);
        $objUser->setPassword($this->objHasher->hashPassword($objUser, 'User1234!'));
        $objUser->setIsVerified(true);
        $objUser->addRegime($arrRegimes[1]);
        $objUser->addRegime($arrRegimes[3]);
        $manager->persist($objUser);

        // =====================
        // RECETTES
        // =====================
        $arrRecettesData = [
            [
                'libelle'      => 'Pâtes à la tomate maison',
                'description'  => 'Une recette simple et savoureuse de pâtes avec une sauce tomate maison.',
                'difficulte'   => 'facile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 20,
                'regime'       => $arrRegimes[1], // Végétarien
            ],
            [
                'libelle'      => 'Poulet rôti aux herbes',
                'description'  => 'Un poulet rôti doré et parfumé aux herbes de Provence.',
                'difficulte'   => 'moyen',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 60,
                'regime'       => $arrRegimes[0], // Omnivore
            ],
            [
                'libelle'      => 'Mousse au chocolat',
                'description'  => 'Une mousse au chocolat onctueuse et légère, prête en 20 minutes.',
                'difficulte'   => 'facile',
                'portion'      => 6,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 0,
                'regime'       => $arrRegimes[1], // Végétarien
            ],
            [
                'libelle'      => 'Soupe de tomates basilic',
                'description'  => 'Une soupe veloutée de tomates fraîches au basilic.',
                'difficulte'   => 'facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 25,
                'regime'       => $arrRegimes[2], // Vegan
            ],
            [
                'libelle'      => 'Crêpes maison',
                'description'  => 'La recette traditionnelle des crêpes bretonnes.',
                'difficulte'   => 'facile',
                'portion'      => 8,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 20,
                'regime'       => $arrRegimes[1], // Végétarien
            ],
        ];

        foreach ($arrRecettesData as $arrData) {
            $objRecette = new Recette();
            $objRecette->setRecetteLibelle($arrData['libelle']);
            $objRecette->setRecetteDescription($arrData['description']);
            $objRecette->setRecetteDifficulte($arrData['difficulte']);
            $objRecette->setRecettePortion($arrData['portion']);
            $objRecette->setRecetteTempsPrepa($arrData['tempsPrepa']);
            $objRecette->setRecetteTempsCuisson($arrData['tempsCuisson']);
            $manager->persist($objRecette);

            // Etapes
            $objEtape1 = new Etape();
            $objEtape1->setEtapeNumero(1);
            $objEtape1->setEtapeLibelle('Préparation');
            $objEtape1->setEtapeDescription('Préparer tous les ingrédients nécessaires.');
            $objEtape1->setEtapeDuree(5);
            $objEtape1->setRecette($objRecette);
            $manager->persist($objEtape1);

            $objEtape2 = new Etape();
            $objEtape2->setEtapeNumero(2);
            $objEtape2->setEtapeLibelle('Cuisson');
            $objEtape2->setEtapeDescription('Suivre les étapes de cuisson selon la recette.');
            $objEtape2->setEtapeDuree($arrData['tempsCuisson']);
            $objEtape2->setRecette($objRecette);
            $manager->persist($objEtape2);

            // Like de l'user test
            $objLike = new LikeRecette();
            $objLike->setLikeDate(new \DateTimeImmutable());
            $objLike->setLikeUser($objUser);
            $objLike->setLikeRecette($objRecette);
            $manager->persist($objLike);
        }

        $manager->flush();
    }
}