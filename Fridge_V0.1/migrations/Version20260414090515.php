<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414090515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recette_regime (recette_id INT NOT NULL, regime_id INT NOT NULL, PRIMARY KEY (recette_id, regime_id))');
        $this->addSql('CREATE INDEX IDX_B316888589312FE9 ON recette_regime (recette_id)');
        $this->addSql('CREATE INDEX IDX_B316888535E7D534 ON recette_regime (regime_id)');
        $this->addSql('ALTER TABLE recette_regime ADD CONSTRAINT FK_B316888589312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette_regime ADD CONSTRAINT FK_B316888535E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recette_regime DROP CONSTRAINT FK_B316888589312FE9');
        $this->addSql('ALTER TABLE recette_regime DROP CONSTRAINT FK_B316888535E7D534');
        $this->addSql('DROP TABLE recette_regime');
    }
}
