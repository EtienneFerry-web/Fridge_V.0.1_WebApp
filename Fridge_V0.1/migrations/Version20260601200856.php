<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601200856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression des colonnes Spoonacular (recette_source, spoonacular_id)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recette DROP recette_source');
        $this->addSql('ALTER TABLE recette DROP spoonacular_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recette ADD recette_source VARCHAR(20) NOT NULL DEFAULT \'user\'');
        $this->addSql('ALTER TABLE recette ADD spoonacular_id INT DEFAULT NULL');
    }
}
