<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430223511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diplome (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, organisme VARCHAR(255) NOT NULL, pays VARCHAR(150) NOT NULL, cadre VARCHAR(10) NOT NULL, echelle VARCHAR(10) NOT NULL, categorie VARCHAR(20) NOT NULL, bonification INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence ADD diplome_reference_id INT DEFAULT NULL, DROP diplome, DROP categorie');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id)');
        $this->addSql('CREATE INDEX IDX_B74B730B6E9EAAB ON equivalence (diplome_reference_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE diplome');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('DROP INDEX IDX_B74B730B6E9EAAB ON equivalence');
        $this->addSql('ALTER TABLE equivalence ADD diplome VARCHAR(255) NOT NULL, ADD categorie VARCHAR(50) DEFAULT NULL, DROP diplome_reference_id');
    }
}
