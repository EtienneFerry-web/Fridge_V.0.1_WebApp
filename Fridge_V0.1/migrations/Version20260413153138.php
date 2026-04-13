<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413153138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

public function up(Schema $schema): void
{
    $this->addSql("ALTER TABLE recette ADD recette_statut VARCHAR(20) NOT NULL DEFAULT 'publie'");
    $this->addSql('ALTER TABLE recette ADD recette_origine VARCHAR(50) DEFAULT NULL');
    $this->addSql('ALTER TABLE recette ADD recette_created_at DATE NOT NULL DEFAULT CURRENT_DATE');
    $this->addSql('ALTER TABLE recette ALTER COLUMN recette_statut DROP DEFAULT');
    $this->addSql('ALTER TABLE recette ALTER COLUMN recette_created_at DROP DEFAULT');
}

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette DROP recette_statut');
        $this->addSql('ALTER TABLE recette DROP recette_origine');
        $this->addSql('ALTER TABLE recette DROP recette_created_at');
    }
}
