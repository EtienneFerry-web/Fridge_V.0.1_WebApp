<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425084919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Ajouter la colonne en NULL autorisé temporairement
        $this->addSql('ALTER TABLE recette ADD recette_source VARCHAR(20) DEFAULT NULL');
        
        // 2. Remplir les lignes existantes avec la valeur par défaut
        $this->addSql("UPDATE recette SET recette_source = 'user' WHERE recette_source IS NULL");
        
        // 3. Passer la colonne en NOT NULL
        $this->addSql('ALTER TABLE recette ALTER COLUMN recette_source SET NOT NULL');
        
        // Les autres colonnes sont déjà nullable, pas de souci
        $this->addSql('ALTER TABLE recette ADD spoonacular_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette ADD source_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recette DROP recette_source');
        $this->addSql('ALTER TABLE recette DROP spoonacular_id');
        $this->addSql('ALTER TABLE recette DROP source_url');
    }
}
