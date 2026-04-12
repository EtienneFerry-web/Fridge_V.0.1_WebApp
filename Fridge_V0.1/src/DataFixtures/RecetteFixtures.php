<?php

namespace App\DataFixtures;

use App\Entity\Etape;
use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RecetteFixtures extends Fixture
{
    const PATES_TOMATE    = 'recette-pates-tomate';
    const POULET_ROTI     = 'recette-poulet-roti';
    const MOUSSE_CHOCO    = 'recette-mousse-chocolat';
    const SOUPE_TOMATES   = 'recette-soupe-tomates';
    const CREPES          = 'recette-crepes';

    // Répertoire de destination des photos (relatif à /public)
    // À adapter selon votre configuration
    const UPLOAD_DIR = '/public/uploads/recettes/';

    public function __construct(private ParameterBagInterface $params) {}

    public function load(ObjectManager $manager): void
    {
        $arrRecettesData = [
            self::PATES_TOMATE => [
                'libelle'      => 'Pâtes à la tomate maison',
                'description'  => 'Une recette simple et savoureuse de pâtes avec une sauce tomate maison.',
                'difficulte'   => 'facile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 20,
                'photo'        => 'https://images.unsplash.com/photo-1588013273468-315fd88ea34c?w=800',
                'photoNom'     => 'pates-tomate.jpg',
                'etapes' => [
                    ['Mise en place',    'Faire bouillir une grande casserole d\'eau salée.',           5],
                    ['Sauce tomate',     'Faire revenir l\'ail et les tomates dans l\'huile d\'olive.', 10],
                    ['Cuisson pâtes',    'Cuire les pâtes al dente selon les indications du paquet.',  10],
                    ['Dressage',         'Mélanger les pâtes avec la sauce et servir avec du basilic.', 2],
                ],
            ],
            self::POULET_ROTI => [
                'libelle'      => 'Poulet rôti aux herbes',
                'description'  => 'Un poulet rôti doré et parfumé aux herbes de Provence.',
                'difficulte'   => 'moyen',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 60,
                'photo'        => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c8?w=800',
                'photoNom'     => 'poulet-roti.jpg',
                'etapes' => [
                    ['Marinade',      'Mélanger huile d\'olive, herbes de Provence, sel et poivre.',    10],
                    ['Préparation',   'Badigeonner le poulet de la marinade et laisser reposer.',        10],
                    ['Cuisson',       'Enfourner à 200°C pendant 60 minutes en arrosant régulièrement.', 60],
                    ['Repos',         'Laisser reposer 10 minutes avant de découper.',                   10],
                ],
            ],
            self::MOUSSE_CHOCO => [
                'libelle'      => 'Mousse au chocolat',
                'description'  => 'Une mousse au chocolat onctueuse et légère, prête en 20 minutes.',
                'difficulte'   => 'facile',
                'portion'      => 6,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 0,
                'photo'        => 'https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=800',
                'photoNom'     => 'mousse-chocolat.jpg',
                'etapes' => [
                    ['Fonte chocolat',  'Faire fondre le chocolat noir au bain-marie.',              5],
                    ['Jaunes d\'oeufs', 'Incorporer les jaunes d\'oeufs au chocolat fondu.',         3],
                    ['Blancs en neige', 'Monter les blancs en neige ferme avec une pincée de sel.',  5],
                    ['Assemblage',      'Incorporer délicatement les blancs au mélange chocolaté.',  5],
                    ['Réfrigération',   'Répartir en verrines et réfrigérer au moins 2 heures.',     5],
                ],
            ],
            self::SOUPE_TOMATES => [
                'libelle'      => 'Soupe de tomates basilic',
                'description'  => 'Une soupe veloutée de tomates fraîches au basilic.',
                'difficulte'   => 'facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 25,
                'photo'        => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=800',
                'photoNom'     => 'soupe-tomates.jpg',
                'etapes' => [
                    ['Préparation', 'Éplucher et couper les tomates et l\'oignon en morceaux.',         5],
                    ['Cuisson',     'Faire revenir l\'oignon puis ajouter les tomates et l\'ail.',      10],
                    ['Mijotage',    'Laisser mijoter 20 minutes à feu doux avec le bouillon.',          20],
                    ['Mixage',      'Mixer la soupe et ajuster l\'assaisonnement. Ajouter le basilic.', 3],
                ],
            ],
            self::CREPES => [
                'libelle'      => 'Crêpes maison',
                'description'  => 'La recette traditionnelle des crêpes bretonnes.',
                'difficulte'   => 'facile',
                'portion'      => 8,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 20,
                'photo'        => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=800',
                'photoNom'     => 'crepes-maison.jpg',
                'etapes' => [
                    ['Pâte',    'Mélanger farine, oeufs, lait et beurre fondu jusqu\'à obtenir une pâte lisse.', 5],
                    ['Repos',   'Laisser reposer la pâte 30 minutes au réfrigérateur.',                          30],
                    ['Cuisson', 'Cuire les crêpes dans une poêle beurrée à feu moyen.',                          20],
                    ['Service', 'Servir avec garnitures sucrées ou salées au choix.',                             2],
                ],
            ],
        ];

        // Création du répertoire d'upload si inexistant
        $strUploadPath = $this->params->get('kernel.project_dir') . self::UPLOAD_DIR;
        if (!is_dir($strUploadPath)) {
            mkdir($strUploadPath, 0755, true);
        }

        foreach ($arrRecettesData as $strReference => $arrData) {

            // Téléchargement de la photo depuis Unsplash
            $strPhotoNom = $arrData['photoNom'];
            $strPhotoDest = $strUploadPath . $strPhotoNom;

            if (!file_exists($strPhotoDest)) {
                $binContenu = @file_get_contents($arrData['photo']);
                if ($binContenu !== false) {
                    file_put_contents($strPhotoDest, $binContenu);
                } else {
                    // Fallback : laisser la photo vide plutôt que planter
                    $strPhotoNom = null;
                }
            }

            $objRecette = new Recette();
            $objRecette->setRecetteLibelle($arrData['libelle'])
                       ->setRecetteDescription($arrData['description'])
                       ->setRecetteDifficulte($arrData['difficulte'])
                       ->setRecettePortion($arrData['portion'])
                       ->setRecetteTempsPrepa($arrData['tempsPrepa'])
                       ->setRecetteTempsCuisson($arrData['tempsCuisson'])
                       ->setRecettePhoto($strPhotoNom);   // ← nouveau champ
            $manager->persist($objRecette);

            // Etapes
            foreach ($arrData['etapes'] as $intNumero => [$strLibelle, $strDescription, $intDuree]) {
                $objEtape = new Etape();
                $objEtape->setEtapeNumero($intNumero + 1)
                         ->setEtapeLibelle($strLibelle)
                         ->setEtapeDescription($strDescription)
                         ->setEtapeDuree($intDuree)
                         ->setRecette($objRecette);
                $manager->persist($objEtape);
            }

            $this->addReference($strReference, $objRecette);
        }

        $manager->flush();
    }
}