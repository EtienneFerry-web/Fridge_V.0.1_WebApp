<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417073422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette ADD created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB6390DE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (user_id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_49BB6390DE12AB56 ON recette (created_by)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette DROP CONSTRAINT FK_49BB6390DE12AB56');
        $this->addSql('DROP INDEX IDX_49BB6390DE12AB56');
        $this->addSql('ALTER TABLE recette DROP created_by');
    }
}
