<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409120808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD user_date_inscription TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('UPDATE "user" SET user_date_inscription = NOW()');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN user_date_inscription SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD user_date_suppression TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP user_date_inscription');
        $this->addSql('ALTER TABLE "user" DROP user_date_suppression');
    }
}
