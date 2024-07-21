<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718102300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, owner_id INT NOT NULL, montant NUMERIC(20, 2) NOT NULL, rappel1 TEXT NOT NULL, montant1 NUMERIC(15, 2) NOT NULL, montant2 NUMERIC(15, 2) DEFAULT NULL, rappel2 TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73F2F77B7E3C61F9 ON budget (owner_id)');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B7E3C61F9');
        $this->addSql('DROP TABLE budget');
    }
}
