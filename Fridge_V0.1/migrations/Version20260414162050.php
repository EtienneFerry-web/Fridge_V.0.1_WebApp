<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414162050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contenir ADD contenir_unite VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE contenir ADD recette_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contenir ALTER contenir_quantite TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE contenir ALTER contenir_quantite DROP NOT NULL');
        $this->addSql('ALTER TABLE contenir ADD CONSTRAINT FK_3C914DFD89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3C914DFD89312FE9 ON contenir (recette_id)');
        $this->addSql('ALTER TABLE liste_course ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE liste_course ADD CONSTRAINT FK_27EF1A82A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (user_id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_27EF1A82A76ED395 ON liste_course (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contenir DROP CONSTRAINT FK_3C914DFD89312FE9');
        $this->addSql('DROP INDEX IDX_3C914DFD89312FE9');
        $this->addSql('ALTER TABLE contenir DROP contenir_unite');
        $this->addSql('ALTER TABLE contenir DROP recette_id');
        $this->addSql('ALTER TABLE contenir ALTER contenir_quantite TYPE INT');
        $this->addSql('ALTER TABLE contenir ALTER contenir_quantite SET NOT NULL');
        $this->addSql('ALTER TABLE liste_course DROP CONSTRAINT FK_27EF1A82A76ED395');
        $this->addSql('DROP INDEX IDX_27EF1A82A76ED395');
        $this->addSql('ALTER TABLE liste_course DROP user_id');
    }
}
