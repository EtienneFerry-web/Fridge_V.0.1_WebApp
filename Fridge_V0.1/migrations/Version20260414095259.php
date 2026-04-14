<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414095259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocker ADD stocker_unite VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE stocker ADD stocker_seuil NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE stocker ADD stocker_date_peremption DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE stocker ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stocker ADD CONSTRAINT FK_AD495DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (user_id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_AD495DA76ED395 ON stocker (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocker DROP CONSTRAINT FK_AD495DA76ED395');
        $this->addSql('DROP INDEX IDX_AD495DA76ED395');
        $this->addSql('ALTER TABLE stocker DROP stocker_unite');
        $this->addSql('ALTER TABLE stocker DROP stocker_seuil');
        $this->addSql('ALTER TABLE stocker DROP stocker_date_peremption');
        $this->addSql('ALTER TABLE stocker DROP user_id');
    }
}
