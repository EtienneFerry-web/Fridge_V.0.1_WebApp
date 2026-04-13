<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IngredientFixtures extends Fixture
{
    const FARINE          = 'ingredient-farine';
    const OEUFS           = 'ingredient-oeufs';
    const LAIT            = 'ingredient-lait';
    const BEURRE          = 'ingredient-beurre';
    const SUCRE           = 'ingredient-sucre';
    const SEL             = 'ingredient-sel';
    const TOMATES         = 'ingredient-tomates';
    const OIGNONS         = 'ingredient-oignons';
    const AIL             = 'ingredient-ail';
    const POULET          = 'ingredient-poulet';
    const PATES           = 'ingredient-pates';
    const HUILE_OLIVE     = 'ingredient-huile-olive';
    const CHOCOLAT_NOIR   = 'ingredient-chocolat-noir';
    const CREME_FRAICHE   = 'ingredient-creme-fraiche';
    const BASILIC         = 'ingredient-basilic';
    const RIZ             = 'ingredient-riz';
    const LARDONS         = 'ingredient-lardons';
    const GRUYERE         = 'ingredient-gruyere';
    const POMME_DE_TERRE  = 'ingredient-pomme-de-terre';
    const SAUMON          = 'ingredient-saumon';
    const CREVETTES       = 'ingredient-crevettes';
    const COURGETTE       = 'ingredient-courgette';
    const AUBERGINE       = 'ingredient-aubergine';
    const POIVRON         = 'ingredient-poivron';
    const CHAMPIGNONS     = 'ingredient-champignons';
    const EPINARDS        = 'ingredient-epinards';
    const CITRON          = 'ingredient-citron';
    const MIEL            = 'ingredient-miel';
    const PARMESAN        = 'ingredient-parmesan';
    const PAIN            = 'ingredient-pain';
    const THON            = 'ingredient-thon';
    const CURRY           = 'ingredient-curry';
    const LAIT_COCO       = 'ingredient-lait-coco';
    const POMME           = 'ingredient-pomme';
    const FRAISE          = 'ingredient-fraise';
    const LEVURE          = 'ingredient-levure';
    const POIVRE          = 'ingredient-poivre';
    const MOUTARDE        = 'ingredient-moutarde';
    const VINAIGRE        = 'ingredient-vinaigre';

    public function load(ObjectManager $manager): void
    {
        $arrIngredients = [
            self::FARINE         => ['Farine',           'Épicerie'],
            self::OEUFS          => ['Oeufs',             'Produits frais'],
            self::LAIT           => ['Lait',              'Produits laitiers'],
            self::BEURRE         => ['Beurre',            'Produits laitiers'],
            self::SUCRE          => ['Sucre',             'Épicerie'],
            self::SEL            => ['Sel',               'Épicerie'],
            self::TOMATES        => ['Tomates',           'Légumes'],
            self::OIGNONS        => ['Oignons',           'Légumes'],
            self::AIL            => ['Ail',               'Légumes'],
            self::POULET         => ['Poulet',            'Viandes'],
            self::PATES          => ['Pâtes',             'Épicerie'],
            self::HUILE_OLIVE    => ['Huile d\'olive',    'Épicerie'],
            self::CHOCOLAT_NOIR  => ['Chocolat noir',     'Épicerie'],
            self::CREME_FRAICHE  => ['Crème fraîche',     'Produits laitiers'],
            self::BASILIC        => ['Basilic',           'Herbes'],
            self::RIZ            => ['Riz',               'Épicerie'],
            self::LARDONS        => ['Lardons',           'Viandes'],
            self::GRUYERE        => ['Gruyère',           'Produits laitiers'],
            self::POMME_DE_TERRE => ['Pommes de terre',   'Légumes'],
            self::SAUMON         => ['Saumon',            'Poissons'],
            self::CREVETTES      => ['Crevettes',         'Poissons'],
            self::COURGETTE      => ['Courgette',         'Légumes'],
            self::AUBERGINE      => ['Aubergine',         'Légumes'],
            self::POIVRON        => ['Poivron',           'Légumes'],
            self::CHAMPIGNONS    => ['Champignons',       'Légumes'],
            self::EPINARDS       => ['Épinards',          'Légumes'],
            self::CITRON         => ['Citron',            'Fruits'],
            self::MIEL           => ['Miel',              'Épicerie'],
            self::PARMESAN       => ['Parmesan',          'Produits laitiers'],
            self::PAIN           => ['Pain',              'Boulangerie'],
            self::THON           => ['Thon en boîte',     'Épicerie'],
            self::CURRY          => ['Curry en poudre',   'Épicerie'],
            self::LAIT_COCO      => ['Lait de coco',      'Épicerie'],
            self::POMME          => ['Pommes',            'Fruits'],
            self::FRAISE         => ['Fraises',           'Fruits'],
            self::LEVURE         => ['Levure chimique',   'Épicerie'],
            self::POIVRE         => ['Poivre',            'Épicerie'],
            self::MOUTARDE       => ['Moutarde',          'Épicerie'],
            self::VINAIGRE       => ['Vinaigre balsamique','Épicerie'],
        ];

        foreach ($arrIngredients as $strReference => [$strLibelle, $strType]) {
            $objIngredient = new Ingredient();
            $objIngredient->setIngredientLibelle($strLibelle);
            $objIngredient->setIngredientType($strType);
            $manager->persist($objIngredient);
            $this->addReference($strReference, $objIngredient);
        }

        $manager->flush();
    }
}