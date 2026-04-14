<?php

namespace App\DataFixtures;

use App\Entity\Contenir;
use App\Entity\Etape;
use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    const PATES_TOMATE      = 'recette-pates-tomate';
    const POULET_ROTI       = 'recette-poulet-roti';
    const MOUSSE_CHOCO      = 'recette-mousse-chocolat';
    const SOUPE_TOMATES     = 'recette-soupe-tomates';
    const CREPES            = 'recette-crepes';
    const RISOTTO           = 'recette-risotto';
    const TARTE_POMME       = 'recette-tarte-pomme';
    const CURRY_LEGUMES     = 'recette-curry-legumes';
    const QUICHE_LORRAINE   = 'recette-quiche-lorraine';
    const RATATOUILLE       = 'recette-ratatouille';
    const SALADE_NICOISE    = 'recette-salade-nicoise';
    const GATEAU_CHOCO      = 'recette-gateau-chocolat';
    const OMELETTE          = 'recette-omelette';
    const GRATIN_DAUPHINOIS = 'recette-gratin-dauphinois';
    const SAUMON_CITRON     = 'recette-saumon-citron';
    const PAD_THAI          = 'recette-pad-thai';
    const BOLOGNESE         = 'recette-bolognese';
    const TIRAMISU          = 'recette-tiramisu';
    const VELOUTE_COURGETTE = 'recette-veloute-courgette';
    const POULET_CURRY      = 'recette-poulet-curry';
    const TARTE_CITRON      = 'recette-tarte-citron';
    const BRUSCHETTA        = 'recette-bruschetta';
    const RIZ_CANTONAIS     = 'recette-riz-cantonais';
    const CREVETTES_AIL     = 'recette-crevettes-ail';
    const FONDANT_CHOCO     = 'recette-fondant-chocolat';
    const SALADE_CAESAR     = 'recette-salade-caesar';
    const CLAFOUTIS_FRAISE  = 'recette-clafoutis-fraise';
    const POELE_CHAMPIGNONS = 'recette-poele-champignons';
    const SANDWICH_THON     = 'recette-sandwich-thon';
    const SMOOTHIE_FRAISE   = 'recette-smoothie-fraise';

    const UPLOAD_DIR = '/public/uploads/recettes/';

    public function __construct(private ParameterBagInterface $params) {}

    public function getDependencies(): array
    {
        return [RegimeFixtures::class, IngredientFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $arrRecettesData = [

            self::PATES_TOMATE => [
                'libelle'      => 'Pâtes à la tomate maison',
                'description'  => 'Une recette simple et savoureuse de pâtes avec une sauce tomate maison.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 20,
                'statut'       => 'publie',
                'origine'      => 'it',
                'photo'        => 'https://images.unsplash.com/photo-1588013273468-315fd88ea34c?w=800',
                'photoNom'     => 'pates-tomate.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Mise en place',  'Faire bouillir une grande casserole d\'eau salée.',            5],
                    ['Sauce tomate',   'Faire revenir l\'ail et les tomates dans l\'huile d\'olive.',  10],
                    ['Cuisson pâtes',  'Cuire les pâtes al dente selon les indications du paquet.',    10],
                    ['Dressage',       'Mélanger les pâtes avec la sauce et servir avec du basilic.',   2],
                ],
                'ingredients' => [
                    [IngredientFixtures::PATES,       400, 'g'],
                    [IngredientFixtures::TOMATES,     500, 'g'],
                    [IngredientFixtures::AIL,           3, 'gousse(s)'],
                    [IngredientFixtures::HUILE_OLIVE,   3, 'tbsp'],
                    [IngredientFixtures::BASILIC,       1, 'bouquet'],
                    [IngredientFixtures::SEL,           1, 'tsp'],
                    [IngredientFixtures::POIVRE,        1, 'pincée'],
                ],
            ],

            self::POULET_ROTI => [
                'libelle'      => 'Poulet rôti aux herbes',
                'description'  => 'Un poulet rôti doré et parfumé aux herbes de Provence.',
                'difficulte'   => 'Moyen',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 60,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c8?w=800',
                'photoNom'     => 'poulet-roti.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Marinade',    'Mélanger huile d\'olive, herbes de Provence, sel et poivre.',     10],
                    ['Préparation', 'Badigeonner le poulet de la marinade et laisser reposer.',         10],
                    ['Cuisson',     'Enfourner à 200°C pendant 60 minutes en arrosant régulièrement.', 60],
                    ['Repos',       'Laisser reposer 10 minutes avant de découper.',                   10],
                ],
                'ingredients' => [
                    [IngredientFixtures::POULET,      1500, 'g'],
                    [IngredientFixtures::HUILE_OLIVE,    4, 'tbsp'],
                    [IngredientFixtures::AIL,            4, 'gousse(s)'],
                    [IngredientFixtures::BEURRE,        30, 'g'],
                    [IngredientFixtures::SEL,            2, 'tsp'],
                    [IngredientFixtures::POIVRE,         1, 'tsp'],
                ],
            ],

            self::MOUSSE_CHOCO => [
                'libelle'      => 'Mousse au chocolat',
                'description'  => 'Une mousse au chocolat onctueuse et légère, prête en 20 minutes.',
                'difficulte'   => 'Facile',
                'portion'      => 6,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 0,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?w=800',
                'photoNom'     => 'mousse-chocolat.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Fonte chocolat',  'Faire fondre le chocolat noir au bain-marie.',             5],
                    ['Jaunes d\'oeufs', 'Incorporer les jaunes d\'oeufs au chocolat fondu.',        3],
                    ['Blancs en neige', 'Monter les blancs en neige ferme avec une pincée de sel.', 5],
                    ['Assemblage',      'Incorporer délicatement les blancs au mélange chocolaté.', 5],
                    ['Réfrigération',   'Répartir en verrines et réfrigérer au moins 2 heures.',    5],
                ],
                'ingredients' => [
                    [IngredientFixtures::CHOCOLAT_NOIR, 200, 'g'],
                    [IngredientFixtures::OEUFS,           6, null],
                    [IngredientFixtures::SUCRE,          60, 'g'],
                    [IngredientFixtures::BEURRE,         30, 'g'],
                    [IngredientFixtures::SEL,             1, 'pincée'],
                ],
            ],

            self::SOUPE_TOMATES => [
                'libelle'      => 'Soupe de tomates basilic',
                'description'  => 'Une soupe veloutée de tomates fraîches au basilic.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 25,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=800',
                'photoNom'     => 'soupe-tomates.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::VEGAN],
                'etapes' => [
                    ['Préparation', 'Éplucher et couper les tomates et l\'oignon en morceaux.',         5],
                    ['Cuisson',     'Faire revenir l\'oignon puis ajouter les tomates et l\'ail.',      10],
                    ['Mijotage',    'Laisser mijoter 20 minutes à feu doux avec le bouillon.',          20],
                    ['Mixage',      'Mixer la soupe et ajuster l\'assaisonnement. Ajouter le basilic.', 3],
                ],
                'ingredients' => [
                    [IngredientFixtures::TOMATES,     800, 'g'],
                    [IngredientFixtures::OIGNONS,       2, null],
                    [IngredientFixtures::AIL,           3, 'gousse(s)'],
                    [IngredientFixtures::HUILE_OLIVE,   2, 'tbsp'],
                    [IngredientFixtures::BASILIC,       1, 'bouquet'],
                    [IngredientFixtures::SEL,           1, 'tsp'],
                    [IngredientFixtures::POIVRE,        1, 'pincée'],
                ],
            ],

            self::CREPES => [
                'libelle'      => 'Crêpes maison',
                'description'  => 'La recette traditionnelle des crêpes bretonnes.',
                'difficulte'   => 'Facile',
                'portion'      => 8,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 20,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=800',
                'photoNom'     => 'crepes-maison.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Pâte',    'Mélanger farine, oeufs, lait et beurre fondu jusqu\'à obtenir une pâte lisse.', 5],
                    ['Repos',   'Laisser reposer la pâte 30 minutes au réfrigérateur.',                          30],
                    ['Cuisson', 'Cuire les crêpes dans une poêle beurrée à feu moyen.',                          20],
                    ['Service', 'Servir avec garnitures sucrées ou salées au choix.',                             2],
                ],
                'ingredients' => [
                    [IngredientFixtures::FARINE, 250, 'g'],
                    [IngredientFixtures::OEUFS,    3, null],
                    [IngredientFixtures::LAIT,   500, 'ml'],
                    [IngredientFixtures::BEURRE,  50, 'g'],
                    [IngredientFixtures::SEL,      1, 'pincée'],
                    [IngredientFixtures::SUCRE,   20, 'g'],
                ],
            ],

            self::RISOTTO => [
                'libelle'      => 'Risotto aux champignons',
                'description'  => 'Un risotto crémeux aux champignons parfumé au parmesan.',
                'difficulte'   => 'Difficile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 35,
                'statut'       => 'publie',
                'origine'      => 'it',
                'photo'        => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=800',
                'photoNom'     => 'risotto-champignons.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Bouillon',       'Chauffer le bouillon de légumes dans une casserole à part.',             5],
                    ['Soffritto',      'Faire revenir l\'oignon dans le beurre jusqu\'à transparence.',          5],
                    ['Toast du riz',   'Ajouter le riz et faire toaster 2 minutes en remuant.',                  3],
                    ['Cuisson du riz', 'Ajouter le bouillon louche par louche en remuant constamment.',         25],
                    ['Finition',       'Incorporer parmesan, beurre et champignons sautés.',                     5],
                ],
                'ingredients' => [
                    [IngredientFixtures::RIZ,         320, 'g'],
                    [IngredientFixtures::CHAMPIGNONS,  300, 'g'],
                    [IngredientFixtures::OIGNONS,        1, null],
                    [IngredientFixtures::AIL,            2, 'gousse(s)'],
                    [IngredientFixtures::BEURRE,        60, 'g'],
                    [IngredientFixtures::PARMESAN,      80, 'g'],
                    [IngredientFixtures::HUILE_OLIVE,    2, 'tbsp'],
                    [IngredientFixtures::SEL,            1, 'tsp'],
                    [IngredientFixtures::POIVRE,         1, 'pincée'],
                ],
            ],

            self::TARTE_POMME => [
                'libelle'      => 'Tarte aux pommes classique',
                'description'  => 'Une tarte aux pommes dorée avec une pâte brisée maison.',
                'difficulte'   => 'Moyen',
                'portion'      => 8,
                'tempsPrepa'   => 30,
                'tempsCuisson' => 40,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1568571780765-9276f4b1e57f?w=800',
                'photoNom'     => 'tarte-pomme.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Pâte brisée', 'Mélanger farine, beurre froid et eau pour former la pâte. Réfrigérer 30 min.', 15],
                    ['Préparation', 'Éplucher les pommes et les couper en fines lamelles.',                           10],
                    ['Fonçage',     'Étaler la pâte et foncer le moule. Piquer le fond.',                             5],
                    ['Garniture',   'Disposer les lamelles de pommes en rosace sur la pâte.',                         5],
                    ['Cuisson',     'Enfourner à 180°C pendant 40 minutes jusqu\'à dorure.',                         40],
                ],
                'ingredients' => [
                    [IngredientFixtures::POMME,   1000, 'g'],
                    [IngredientFixtures::FARINE,   250, 'g'],
                    [IngredientFixtures::BEURRE,   125, 'g'],
                    [IngredientFixtures::SUCRE,    100, 'g'],
                    [IngredientFixtures::OEUFS,      1, null],
                    [IngredientFixtures::SEL,        1, 'pincée'],
                    [IngredientFixtures::MIEL,       2, 'tbsp'],
                ],
            ],

            self::CURRY_LEGUMES => [
                'libelle'      => 'Curry de légumes au lait de coco',
                'description'  => 'Un curry végétarien parfumé aux épices, doux et onctueux.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 25,
                'statut'       => 'publie',
                'origine'      => 'asia',
                'photo'        => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=800',
                'photoNom'     => 'curry-legumes.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::VEGAN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Préparation', 'Couper courgettes, poivrons et aubergines en dés réguliers.',    10],
                    ['Épices',      'Faire revenir oignon, ail et curry en poudre dans l\'huile.',     5],
                    ['Légumes',     'Ajouter les légumes et faire revenir 5 minutes.',                 5],
                    ['Sauce',       'Verser le lait de coco et laisser mijoter 20 minutes.',          20],
                    ['Service',     'Servir avec du riz basmati et de la coriandre fraîche.',          2],
                ],
                'ingredients' => [
                    [IngredientFixtures::COURGETTE,   2, null],
                    [IngredientFixtures::AUBERGINE,   1, null],
                    [IngredientFixtures::POIVRON,     2, null],
                    [IngredientFixtures::OIGNONS,     1, null],
                    [IngredientFixtures::AIL,         3, 'gousse(s)'],
                    [IngredientFixtures::CURRY,       2, 'tbsp'],
                    [IngredientFixtures::LAIT_COCO, 400, 'ml'],
                    [IngredientFixtures::HUILE_OLIVE, 2, 'tbsp'],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::RIZ,       300, 'g'],
                ],
            ],

            self::QUICHE_LORRAINE => [
                'libelle'      => 'Quiche lorraine traditionnelle',
                'description'  => 'La vraie quiche lorraine avec lardons, crème et gruyère.',
                'difficulte'   => 'Moyen',
                'portion'      => 6,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 35,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800',
                'photoNom'     => 'quiche-lorraine.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Pâte',       'Étaler la pâte brisée et foncer le moule. Précuire 10 minutes à blanc.', 10],
                    ['Appareil',   'Battre les oeufs avec la crème fraîche, sel, poivre et muscade.',         5],
                    ['Garniture',  'Faire revenir les lardons et les disposer sur le fond de tarte.',          5],
                    ['Assemblage', 'Verser l\'appareil sur les lardons et parsemer de gruyère râpé.',          5],
                    ['Cuisson',    'Enfourner à 180°C pendant 35 minutes jusqu\'à prise et dorure.',          35],
                ],
                'ingredients' => [
                    [IngredientFixtures::FARINE,        250, 'g'],
                    [IngredientFixtures::BEURRE,        125, 'g'],
                    [IngredientFixtures::OEUFS,           4, null],
                    [IngredientFixtures::CREME_FRAICHE, 200, 'ml'],
                    [IngredientFixtures::LARDONS,       200, 'g'],
                    [IngredientFixtures::GRUYERE,       100, 'g'],
                    [IngredientFixtures::SEL,             1, 'tsp'],
                    [IngredientFixtures::POIVRE,          1, 'pincée'],
                ],
            ],

            self::RATATOUILLE => [
                'libelle'      => 'Ratatouille provençale',
                'description'  => 'La ratatouille traditionnelle avec ses légumes du soleil.',
                'difficulte'   => 'Moyen',
                'portion'      => 6,
                'tempsPrepa'   => 30,
                'tempsCuisson' => 45,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1572453800999-e8d2d1589b7c?w=800',
                'photoNom'     => 'ratatouille.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::VEGAN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Préparation',    'Couper aubergines, courgettes, poivrons et tomates en rondelles.',   20],
                    ['Cuisson base',   'Faire revenir oignon et ail dans l\'huile d\'olive.',                 5],
                    ['Légumes',        'Ajouter les légumes couche par couche et mijoter à feu doux.',       40],
                    ['Assaisonnement', 'Ajouter herbes de Provence, sel, poivre et huile d\'olive.',          5],
                ],
                'ingredients' => [
                    [IngredientFixtures::AUBERGINE,   2, null],
                    [IngredientFixtures::COURGETTE,   2, null],
                    [IngredientFixtures::POIVRON,     2, null],
                    [IngredientFixtures::TOMATES,   500, 'g'],
                    [IngredientFixtures::OIGNONS,     2, null],
                    [IngredientFixtures::AIL,         4, 'gousse(s)'],
                    [IngredientFixtures::HUILE_OLIVE, 4, 'tbsp'],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::POIVRE,      1, 'pincée'],
                ],
            ],

            self::SALADE_NICOISE => [
                'libelle'      => 'Salade niçoise',
                'description'  => 'La salade niçoise authentique avec thon, anchois et légumes frais.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 10,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800',
                'photoNom'     => 'salade-nicoise.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Cuisson',     'Cuire les oeufs durs et les haricots verts.',                  10],
                    ['Préparation', 'Couper tomates, poivrons et oignons.',                         10],
                    ['Assemblage',  'Dresser la salade avec tous les ingrédients.',                   5],
                    ['Vinaigrette', 'Préparer une vinaigrette à l\'huile d\'olive et au citron.',    3],
                ],
                'ingredients' => [
                    [IngredientFixtures::THON,       200, 'g'],
                    [IngredientFixtures::OEUFS,        4, null],
                    [IngredientFixtures::TOMATES,      3, null],
                    [IngredientFixtures::POIVRON,      1, null],
                    [IngredientFixtures::OIGNONS,      1, null],
                    [IngredientFixtures::CITRON,       1, null],
                    [IngredientFixtures::HUILE_OLIVE,  3, 'tbsp'],
                    [IngredientFixtures::VINAIGRE,     1, 'tbsp'],
                    [IngredientFixtures::SEL,          1, 'tsp'],
                    [IngredientFixtures::POIVRE,       1, 'pincée'],
                ],
            ],

            self::GATEAU_CHOCO => [
                'libelle'      => 'Gâteau au chocolat moelleux',
                'description'  => 'Un gâteau au chocolat ultra moelleux et fondant.',
                'difficulte'   => 'Facile',
                'portion'      => 8,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 30,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800',
                'photoNom'     => 'gateau-chocolat.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Fonte',    'Faire fondre chocolat et beurre ensemble au micro-ondes.',                5],
                    ['Appareil', 'Mélanger sucre, oeufs puis incorporer le chocolat fondu.',                5],
                    ['Farine',   'Ajouter farine et levure tamisées et mélanger sans travailler la pâte.', 3],
                    ['Cuisson',  'Verser dans un moule beurré et cuire 30 minutes à 170°C.',               30],
                ],
                'ingredients' => [
                    [IngredientFixtures::CHOCOLAT_NOIR, 200, 'g'],
                    [IngredientFixtures::BEURRE,        150, 'g'],
                    [IngredientFixtures::SUCRE,         180, 'g'],
                    [IngredientFixtures::OEUFS,           4, null],
                    [IngredientFixtures::FARINE,         80, 'g'],
                    [IngredientFixtures::LEVURE,          1, 'tsp'],
                ],
            ],

            self::OMELETTE => [
                'libelle'      => 'Omelette aux champignons et fines herbes',
                'description'  => 'Une omelette baveuse aux champignons sautés et aux herbes fraîches.',
                'difficulte'   => 'Facile',
                'portion'      => 2,
                'tempsPrepa'   => 5,
                'tempsCuisson' => 8,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1510693206972-df098062cb71?w=800',
                'photoNom'     => 'omelette-champignons.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Champignons', 'Faire sauter les champignons avec l\'ail dans le beurre.',  5],
                    ['Oeufs',       'Battre les oeufs avec sel, poivre et herbes fraîches.',      2],
                    ['Cuisson',     'Cuire l\'omelette à feu moyen en la laissant baveuse.',      5],
                    ['Garniture',   'Ajouter les champignons et plier l\'omelette.',              1],
                ],
                'ingredients' => [
                    [IngredientFixtures::OEUFS,        4, null],
                    [IngredientFixtures::CHAMPIGNONS, 150, 'g'],
                    [IngredientFixtures::BEURRE,       20, 'g'],
                    [IngredientFixtures::AIL,           1, 'gousse(s)'],
                    [IngredientFixtures::SEL,           1, 'tsp'],
                    [IngredientFixtures::POIVRE,        1, 'pincée'],
                ],
            ],

            self::GRATIN_DAUPHINOIS => [
                'libelle'      => 'Gratin dauphinois',
                'description'  => 'Le gratin dauphinois traditionnel, fondant et crémeux.',
                'difficulte'   => 'Facile',
                'portion'      => 6,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 60,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1621510456681-2330135e5871?w=800',
                'photoNom'     => 'gratin-dauphinois.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Pommes de terre', 'Éplucher et couper les pommes de terre en fines rondelles.',            15],
                    ['Infusion',        'Chauffer la crème avec l\'ail écrasé et la noix de muscade.',            5],
                    ['Montage',         'Disposer les rondelles en couches dans un plat beurré, sel et poivre.', 10],
                    ['Cuisson',         'Verser la crème et cuire 60 minutes à 160°C jusqu\'à dorure.',          60],
                ],
                'ingredients' => [
                    [IngredientFixtures::POMME_DE_TERRE, 1200, 'g'],
                    [IngredientFixtures::CREME_FRAICHE,   400, 'ml'],
                    [IngredientFixtures::LAIT,            200, 'ml'],
                    [IngredientFixtures::AIL,               3, 'gousse(s)'],
                    [IngredientFixtures::BEURRE,           20, 'g'],
                    [IngredientFixtures::SEL,               2, 'tsp'],
                    [IngredientFixtures::POIVRE,            1, 'pincée'],
                ],
            ],

            self::SAUMON_CITRON => [
                'libelle'      => 'Pavé de saumon au citron et aneth',
                'description'  => 'Des pavés de saumon fondants avec une sauce légère au citron.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 12,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800',
                'photoNom'     => 'saumon-citron.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Marinade', 'Mariner le saumon avec jus de citron, huile d\'olive et aneth 15 min.', 15],
                    ['Cuisson',  'Saisir le saumon côté peau 6 min puis retourner 4 min.',                 10],
                    ['Sauce',    'Déglacer la poêle avec le jus de citron restant et la crème fraîche.',    3],
                    ['Service',  'Napper le saumon de sauce et décorer d\'aneth frais.',                    2],
                ],
                'ingredients' => [
                    [IngredientFixtures::SAUMON,        600, 'g'],
                    [IngredientFixtures::CITRON,          2, null],
                    [IngredientFixtures::CREME_FRAICHE, 100, 'ml'],
                    [IngredientFixtures::HUILE_OLIVE,     2, 'tbsp'],
                    [IngredientFixtures::BEURRE,         20, 'g'],
                    [IngredientFixtures::SEL,             1, 'tsp'],
                    [IngredientFixtures::POIVRE,          1, 'pincée'],
                ],
            ],

            self::PAD_THAI => [
                'libelle'      => 'Pad Thaï aux crevettes',
                'description'  => 'Le célèbre plat thaïlandais de nouilles sautées aux crevettes.',
                'difficulte'   => 'Moyen',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 15,
                'statut'       => 'publie',
                'origine'      => 'asia',
                'photo'        => 'https://images.unsplash.com/photo-1559314809-0d155014e29e?w=800',
                'photoNom'     => 'pad-thai.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Trempage',   'Faire tremper les nouilles de riz dans l\'eau tiède 20 minutes.',      20],
                    ['Sauce',      'Mélanger sauce poisson, sauce soja, sucre et jus de citron.',           5],
                    ['Wok',        'Faire chauffer l\'huile dans le wok et faire sauter ail et crevettes.', 5],
                    ['Assemblage', 'Ajouter les nouilles, la sauce et les oeufs brouillés.',                5],
                    ['Service',    'Garnir de cacahuètes concassées, citron vert et ciboulette.',           2],
                ],
                'ingredients' => [
                    [IngredientFixtures::CREVETTES,   300, 'g'],
                    [IngredientFixtures::RIZ,         200, 'g'],
                    [IngredientFixtures::OEUFS,         2, null],
                    [IngredientFixtures::AIL,           3, 'gousse(s)'],
                    [IngredientFixtures::CITRON,        1, null],
                    [IngredientFixtures::SUCRE,        15, 'g'],
                    [IngredientFixtures::HUILE_OLIVE,   2, 'tbsp'],
                    [IngredientFixtures::SEL,           1, 'tsp'],
                ],
            ],

            self::BOLOGNESE => [
                'libelle'      => 'Sauce bolognaise maison',
                'description'  => 'Une sauce bolognaise mijotée longuement avec boeuf haché et tomates.',
                'difficulte'   => 'Moyen',
                'portion'      => 6,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 90,
                'statut'       => 'publie',
                'origine'      => 'it',
                'photo'        => 'https://images.unsplash.com/photo-1622973536968-3ead9e780960?w=800',
                'photoNom'     => 'bolognese.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Soffritto', 'Faire revenir oignon, carotte et céleri finement hachés.',              10],
                    ['Viande',    'Ajouter le boeuf haché et faire dorer en cassant les grumeaux.',        10],
                    ['Tomates',   'Incorporer tomates concassées, vin rouge et concentré de tomate.',       5],
                    ['Mijotage',  'Laisser mijoter à feu très doux pendant 90 minutes en remuant.',        90],
                    ['Service',   'Servir sur des tagliatelles avec parmesan râpé.',                        3],
                ],
                'ingredients' => [
                    [IngredientFixtures::TOMATES,    800, 'g'],
                    [IngredientFixtures::OIGNONS,      2, null],
                    [IngredientFixtures::AIL,          3, 'gousse(s)'],
                    [IngredientFixtures::HUILE_OLIVE,  3, 'tbsp'],
                    [IngredientFixtures::PATES,      500, 'g'],
                    [IngredientFixtures::PARMESAN,    80, 'g'],
                    [IngredientFixtures::SEL,          2, 'tsp'],
                    [IngredientFixtures::POIVRE,       1, 'pincée'],
                ],
            ],

            self::TIRAMISU => [
                'libelle'      => 'Tiramisu classique',
                'description'  => 'Le tiramisu original italien avec mascarpone, savoiardi et café.',
                'difficulte'   => 'Moyen',
                'portion'      => 8,
                'tempsPrepa'   => 30,
                'tempsCuisson' => 0,
                'statut'       => 'publie',
                'origine'      => 'it',
                'photo'        => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800',
                'photoNom'     => 'tiramisu.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Café',    'Préparer un café fort et le laisser refroidir.',                                5],
                    ['Crème',   'Battre jaunes d\'oeufs et sucre jusqu\'à blanchiment, incorporer mascarpone.', 10],
                    ['Blancs',  'Monter les blancs en neige et les incorporer délicatement à la crème.',         8],
                    ['Montage', 'Tremper les biscuits dans le café et alterner couches biscuits/crème.',        10],
                    ['Repos',   'Réfrigérer au moins 4 heures et saupoudrer de cacao avant service.',           5],
                ],
                'ingredients' => [
                    [IngredientFixtures::OEUFS,           6, null],
                    [IngredientFixtures::SUCRE,         150, 'g'],
                    [IngredientFixtures::CREME_FRAICHE, 500, 'g'],
                    [IngredientFixtures::PAIN,          200, 'g'],
                    [IngredientFixtures::SEL,             1, 'pincée'],
                ],
            ],

            self::VELOUTE_COURGETTE => [
                'libelle'      => 'Velouté de courgettes',
                'description'  => 'Un velouté léger et onctueux de courgettes.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 20,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1607330289024-1535c6b4e1c1?w=800',
                'photoNom'     => 'veloute-courgette.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Préparation', 'Laver et couper les courgettes en rondelles sans les éplucher.',  5],
                    ['Cuisson',     'Faire revenir oignon et ail puis ajouter les courgettes.',        5],
                    ['Bouillon',    'Couvrir d\'eau bouillante et cuire 15 minutes.',                 15],
                    ['Mixage',      'Mixer finement, incorporer la crème fraîche et assaisonner.',     3],
                ],
                'ingredients' => [
                    [IngredientFixtures::COURGETTE,     800, 'g'],
                    [IngredientFixtures::OIGNONS,         1, null],
                    [IngredientFixtures::AIL,             2, 'gousse(s)'],
                    [IngredientFixtures::CREME_FRAICHE, 100, 'ml'],
                    [IngredientFixtures::HUILE_OLIVE,     2, 'tbsp'],
                    [IngredientFixtures::SEL,             1, 'tsp'],
                    [IngredientFixtures::POIVRE,          1, 'pincée'],
                ],
            ],

            self::POULET_CURRY => [
                'libelle'      => 'Poulet au curry et lait de coco',
                'description'  => 'Un poulet fondant dans une sauce curry crémeuse au lait de coco.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 30,
                'statut'       => 'publie',
                'origine'      => 'asia',
                'photo'        => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=800',
                'photoNom'     => 'poulet-curry.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Découpe', 'Couper le poulet en morceaux réguliers.',                                5],
                    ['Saisir',  'Faire dorer le poulet dans l\'huile avec l\'oignon et l\'ail.',        10],
                    ['Épices',  'Ajouter curry, cumin et gingembre et faire revenir 2 minutes.',         3],
                    ['Sauce',   'Verser le lait de coco et laisser mijoter 25 minutes à feu doux.',     25],
                    ['Service', 'Servir avec du riz basmati et de la coriandre fraîche.',                2],
                ],
                'ingredients' => [
                    [IngredientFixtures::POULET,    800, 'g'],
                    [IngredientFixtures::LAIT_COCO, 400, 'ml'],
                    [IngredientFixtures::CURRY,       2, 'tbsp'],
                    [IngredientFixtures::OIGNONS,     1, null],
                    [IngredientFixtures::AIL,         3, 'gousse(s)'],
                    [IngredientFixtures::HUILE_OLIVE, 2, 'tbsp'],
                    [IngredientFixtures::RIZ,       300, 'g'],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::POIVRE,      1, 'pincée'],
                ],
            ],

            self::TARTE_CITRON => [
                'libelle'      => 'Tarte au citron meringuée',
                'description'  => 'Une tarte au citron acidulée avec une meringue italienne dorée.',
                'difficulte'   => 'Difficile',
                'portion'      => 8,
                'tempsPrepa'   => 45,
                'tempsCuisson' => 25,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?w=800',
                'photoNom'     => 'tarte-citron.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Pâte sablée',  'Préparer et cuire la pâte sablée à blanc 15 minutes.',                      15],
                    ['Crème citron', 'Mélanger jus de citron, zestes, oeufs, sucre et beurre. Cuire à feu doux.', 15],
                    ['Garnissage',   'Verser la crème citron refroidie sur le fond de tarte.',                      5],
                    ['Meringue',     'Préparer la meringue et la pocher sur la tarte.',                            10],
                    ['Dorure',       'Dorer la meringue au chalumeau ou au four en position grill.',                3],
                ],
                'ingredients' => [
                    [IngredientFixtures::CITRON,  4, null],
                    [IngredientFixtures::OEUFS,   5, null],
                    [IngredientFixtures::SUCRE,  200, 'g'],
                    [IngredientFixtures::BEURRE, 120, 'g'],
                    [IngredientFixtures::FARINE, 200, 'g'],
                    [IngredientFixtures::SEL,      1, 'pincée'],
                ],
            ],

            self::BRUSCHETTA => [
                'libelle'      => 'Bruschetta aux tomates et basilic',
                'description'  => 'Des toasts grillés garnis de tomates fraîches marinées, ail et basilic.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 5,
                'statut'       => 'publie',
                'origine'      => 'it',
                'photo'        => 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=800',
                'photoNom'     => 'bruschetta.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Tomates',    'Couper les tomates en dés, assaisonner avec sel, basilic et huile d\'olive.', 5],
                    ['Pain',       'Griller les tranches de pain et les frotter avec une gousse d\'ail.',          5],
                    ['Assemblage', 'Garnir les toasts de la préparation aux tomates et servir immédiatement.',     2],
                ],
                'ingredients' => [
                    [IngredientFixtures::PAIN,        8, 'tranche(s)'],
                    [IngredientFixtures::TOMATES,   500, 'g'],
                    [IngredientFixtures::AIL,         2, 'gousse(s)'],
                    [IngredientFixtures::BASILIC,     1, 'bouquet'],
                    [IngredientFixtures::HUILE_OLIVE, 3, 'tbsp'],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::POIVRE,      1, 'pincée'],
                    [IngredientFixtures::VINAIGRE,    1, 'tbsp'],
                ],
            ],

            self::RIZ_CANTONAIS => [
                'libelle'      => 'Riz cantonais',
                'description'  => 'Le classique riz cantonais avec oeufs brouillés, petits pois et lardons.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 15,
                'statut'       => 'publie',
                'origine'      => 'asia',
                'photo'        => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=800',
                'photoNom'     => 'riz-cantonais.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Riz',        'Cuire le riz la veille et le réfrigérer pour qu\'il soit bien sec.',         30],
                    ['Lardons',    'Faire revenir les lardons à feu vif jusqu\'à dorure.',                        5],
                    ['Oeufs',      'Pousser les lardons sur le côté et brouiller les oeufs.',                     3],
                    ['Assemblage', 'Ajouter le riz froid, les petits pois et la sauce soja. Sauter à feu vif.',  7],
                ],
                'ingredients' => [
                    [IngredientFixtures::RIZ,       400, 'g'],
                    [IngredientFixtures::LARDONS,   150, 'g'],
                    [IngredientFixtures::OEUFS,       3, null],
                    [IngredientFixtures::HUILE_OLIVE, 2, 'tbsp'],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::POIVRE,      1, 'pincée'],
                ],
            ],

            self::CREVETTES_AIL => [
                'libelle'      => 'Crevettes sautées à l\'ail et au beurre',
                'description'  => 'Des crevettes juteuses sautées dans un beurre aillé et parfumé au persil.',
                'difficulte'   => 'Facile',
                'portion'      => 2,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 8,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=800',
                'photoNom'     => 'crevettes-ail.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Préparation', 'Décortiquer les crevettes et les sécher avec du papier absorbant.',    5],
                    ['Cuisson',     'Faire fondre le beurre et faire sauter les crevettes 3 min par face.', 6],
                    ['Finition',    'Ajouter l\'ail émincé, le persil haché et un filet de citron.',        2],
                ],
                'ingredients' => [
                    [IngredientFixtures::CREVETTES, 400, 'g'],
                    [IngredientFixtures::AIL,         4, 'gousse(s)'],
                    [IngredientFixtures::BEURRE,     50, 'g'],
                    [IngredientFixtures::CITRON,      1, null],
                    [IngredientFixtures::SEL,         1, 'tsp'],
                    [IngredientFixtures::POIVRE,      1, 'pincée'],
                ],
            ],

            self::FONDANT_CHOCO => [
                'libelle'      => 'Fondant au chocolat coeur coulant',
                'description'  => 'Le fondant au chocolat avec son coeur coulant.',
                'difficulte'   => 'Difficile',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 12,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800',
                'photoNom'     => 'fondant-chocolat.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Préparation', 'Beurrer et fariner les ramequins. Préchauffer le four à 200°C.',    5],
                    ['Fonte',       'Faire fondre chocolat et beurre ensemble.',                          5],
                    ['Appareil',    'Battre oeufs et sucre, incorporer le chocolat puis la farine.',      5],
                    ['Cuisson',     'Verser dans les ramequins et cuire exactement 12 minutes.',         12],
                    ['Service',     'Démouler délicatement et servir immédiatement avec glace vanille.',  2],
                ],
                'ingredients' => [
                    [IngredientFixtures::CHOCOLAT_NOIR, 150, 'g'],
                    [IngredientFixtures::BEURRE,        100, 'g'],
                    [IngredientFixtures::OEUFS,           3, null],
                    [IngredientFixtures::SUCRE,         100, 'g'],
                    [IngredientFixtures::FARINE,         40, 'g'],
                    [IngredientFixtures::SEL,             1, 'pincée'],
                ],
            ],

            self::SALADE_CAESAR => [
                'libelle'      => 'Salade Caesar au poulet grillé',
                'description'  => 'La salade Caesar avec poulet grillé, croûtons maison et sauce César crémeuse.',
                'difficulte'   => 'Moyen',
                'portion'      => 4,
                'tempsPrepa'   => 20,
                'tempsCuisson' => 15,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1512852939750-1305098529bf?w=800',
                'photoNom'     => 'salade-caesar.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Croûtons',   'Couper le pain en dés et les faire dorer à la poêle avec ail et huile.',   8],
                    ['Poulet',     'Griller les filets de poulet assaisonnés et les couper en lamelles.',      15],
                    ['Sauce',      'Préparer la sauce Caesar : ail, anchois, citron, parmesan, crème.',         5],
                    ['Assemblage', 'Mélanger la salade avec sauce, croûtons, poulet et copeaux de parmesan.',  3],
                ],
                'ingredients' => [
                    [IngredientFixtures::POULET,        500, 'g'],
                    [IngredientFixtures::PAIN,          100, 'g'],
                    [IngredientFixtures::PARMESAN,       80, 'g'],
                    [IngredientFixtures::AIL,             2, 'gousse(s)'],
                    [IngredientFixtures::CITRON,          1, null],
                    [IngredientFixtures::CREME_FRAICHE,  80, 'ml'],
                    [IngredientFixtures::HUILE_OLIVE,     3, 'tbsp'],
                    [IngredientFixtures::MOUTARDE,        1, 'tbsp'],
                    [IngredientFixtures::SEL,             1, 'tsp'],
                    [IngredientFixtures::POIVRE,          1, 'pincée'],
                ],
            ],

            self::CLAFOUTIS_FRAISE => [
                'libelle'      => 'Clafoutis aux fraises',
                'description'  => 'Un clafoutis moelleux aux fraises fraîches.',
                'difficulte'   => 'Facile',
                'portion'      => 6,
                'tempsPrepa'   => 15,
                'tempsCuisson' => 35,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=800',
                'photoNom'     => 'clafoutis-fraise.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN],
                'etapes' => [
                    ['Appareil', 'Mixer oeufs, sucre, farine, lait et beurre fondu jusqu\'à pâte lisse.',  5],
                    ['Fraises',  'Laver, équeuter et couper les fraises en deux.',                          5],
                    ['Montage',  'Beurrer le plat, disposer les fraises et verser l\'appareil.',            3],
                    ['Cuisson',  'Cuire 35 minutes à 180°C jusqu\'à dorure et prise de l\'appareil.',      35],
                ],
                'ingredients' => [
                    [IngredientFixtures::FRAISE,  500, 'g'],
                    [IngredientFixtures::OEUFS,     3, null],
                    [IngredientFixtures::SUCRE,   100, 'g'],
                    [IngredientFixtures::FARINE,   60, 'g'],
                    [IngredientFixtures::LAIT,    250, 'ml'],
                    [IngredientFixtures::BEURRE,   30, 'g'],
                    [IngredientFixtures::SEL,       1, 'pincée'],
                ],
            ],

            self::POELE_CHAMPIGNONS => [
                'libelle'      => 'Poêlée de champignons à l\'ail et persil',
                'description'  => 'Une poêlée de champignons dorés et parfumés à l\'ail.',
                'difficulte'   => 'Facile',
                'portion'      => 4,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 10,
                'statut'       => 'publie',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800',
                'photoNom'     => 'poele-champignons.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::VEGAN, RegimeFixtures::SANS_GLUTEN],
                'etapes' => [
                    ['Nettoyage', 'Nettoyer les champignons avec un papier humide et les couper en quartiers.', 5],
                    ['Cuisson',   'Faire chauffer beurre et huile à feu vif et saisir les champignons.',        8],
                    ['Finition',  'Ajouter ail et persil en fin de cuisson, assaisonner.',                       2],
                ],
                'ingredients' => [
                    [IngredientFixtures::CHAMPIGNONS, 600, 'g'],
                    [IngredientFixtures::AIL,           3, 'gousse(s)'],
                    [IngredientFixtures::BEURRE,       30, 'g'],
                    [IngredientFixtures::HUILE_OLIVE,   2, 'tbsp'],
                    [IngredientFixtures::SEL,           1, 'tsp'],
                    [IngredientFixtures::POIVRE,        1, 'pincée'],
                ],
            ],

            self::SANDWICH_THON => [
                'libelle'      => 'Sandwich au thon et crudités',
                'description'  => 'Un sandwich généreux au thon avec légumes croquants.',
                'difficulte'   => 'Facile',
                'portion'      => 2,
                'tempsPrepa'   => 10,
                'tempsCuisson' => 0,
                'statut'       => 'en_attente',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800',
                'photoNom'     => 'sandwich-thon.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE],
                'etapes' => [
                    ['Garniture', 'Égoutter le thon et le mélanger avec moutarde, citron et poivre.',  3],
                    ['Légumes',   'Couper tomates, concombre et oignons en fines rondelles.',           5],
                    ['Montage',   'Assembler le sandwich et servir avec des chips.',                     2],
                ],
                'ingredients' => [
                    [IngredientFixtures::THON,     200, 'g'],
                    [IngredientFixtures::PAIN,       4, 'tranche(s)'],
                    [IngredientFixtures::TOMATES,    2, null],
                    [IngredientFixtures::OIGNONS,    1, null],
                    [IngredientFixtures::MOUTARDE,   1, 'tbsp'],
                    [IngredientFixtures::CITRON,     1, null],
                    [IngredientFixtures::POIVRE,     1, 'pincée'],
                ],
            ],

            self::SMOOTHIE_FRAISE => [
                'libelle'      => 'Smoothie fraises et banane',
                'description'  => 'Un smoothie onctueux aux fraises et banane.',
                'difficulte'   => 'Facile',
                'portion'      => 2,
                'tempsPrepa'   => 5,
                'tempsCuisson' => 0,
                'statut'       => 'en_attente',
                'origine'      => 'fr',
                'photo'        => 'https://images.unsplash.com/photo-1502741224143-90386d7f8c82?w=800',
                'photoNom'     => 'smoothie-fraise.jpg',
                'regimes'      => [RegimeFixtures::OMNIVORE, RegimeFixtures::VEGETARIEN, RegimeFixtures::VEGAN, RegimeFixtures::SANS_LACTOSE],
                'etapes' => [
                    ['Préparation', 'Laver et équeuter les fraises, peler la banane.',                   2],
                    ['Mixage',      'Mixer fraises, banane, lait et miel jusqu\'à consistance lisse.',    3],
                ],
                'ingredients' => [
                    [IngredientFixtures::FRAISE, 300, 'g'],
                    [IngredientFixtures::LAIT,   200, 'ml'],
                    [IngredientFixtures::MIEL,     2, 'tbsp'],
                    [IngredientFixtures::SUCRE,   10, 'g'],
                ],
            ],
        ];

        $strUploadPath = $this->params->get('kernel.project_dir') . self::UPLOAD_DIR;
        if (!is_dir($strUploadPath)) {
            mkdir($strUploadPath, 0755, true);
        }

        foreach ($arrRecettesData as $strReference => $arrData) {

            $strPhotoNom  = $arrData['photoNom'];
            $strPhotoDest = $strUploadPath . $strPhotoNom;

            if (!file_exists($strPhotoDest)) {
                $binContenu = @file_get_contents($arrData['photo']);
                if ($binContenu !== false) {
                    file_put_contents($strPhotoDest, $binContenu);
                } else {
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
                       ->setRecetteStatut($arrData['statut'])
                       ->setRecetteOrigine($arrData['origine'])
                       ->setRecettePhoto($strPhotoNom);

            foreach ($arrData['regimes'] as $strRegimeRef) {
                $objRegime = $this->getReference($strRegimeRef, \App\Entity\Regime::class);
                $objRecette->addRegime($objRegime);
            }

            $manager->persist($objRecette);

            foreach ($arrData['etapes'] as $intNumero => [$strLibelle, $strDescription, $intDuree]) {
                $objEtape = new Etape();
                $objEtape->setEtapeNumero($intNumero + 1)
                         ->setEtapeLibelle($strLibelle)
                         ->setEtapeDescription($strDescription)
                         ->setEtapeDuree($intDuree)
                         ->setRecette($objRecette);
                $manager->persist($objEtape);
            }

            // Ingrédients de la recette via Contenir (listeCourse = null = template recette)
            foreach ($arrData['ingredients'] as [$strIngredientRef, $fltQuantite, $strUnite]) {
                $objIngredient = $this->getReference($strIngredientRef, \App\Entity\Ingredient::class);
                $objContenir   = new Contenir();
                $objContenir->setIngredient($objIngredient)
                            ->setContenirQuantite($fltQuantite)
                            ->setContenirUnite($strUnite)
                            ->setRecette($objRecette);
                // listeCourse volontairement null : ces lignes sont le template de la recette
                $manager->persist($objContenir);
            }

            $this->addReference($strReference, $objRecette);
        }

        $manager->flush();
    }
}