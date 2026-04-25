<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425095213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend nullable difficulte/portion/temps de Recette + ajoute contenir_libelle_brut pour les imports Spoonacular';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contenir ADD contenir_libelle_brut VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_difficulte DROP NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_portion DROP NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_temps_prepa DROP NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_temps_cuisson DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contenir DROP contenir_libelle_brut');
        $this->addSql('ALTER TABLE recette ALTER recette_difficulte SET NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_portion SET NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_temps_prepa SET NOT NULL');
        $this->addSql('ALTER TABLE recette ALTER recette_temps_cuisson SET NOT NULL');
    }
}