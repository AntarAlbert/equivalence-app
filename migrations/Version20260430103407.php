<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430103407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, equivalence_id INT DEFAULT NULL, INDEX IDX_D8698A76BEC6ADF1 (equivalence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE equivalence (id INT AUTO_INCREMENT NOT NULL, numero_dossier VARCHAR(50) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, diplome VARCHAR(255) NOT NULL, universite VARCHAR(255) NOT NULL, pays VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, decision VARCHAR(255) DEFAULT NULL, classement VARCHAR(255) DEFAULT NULL, observation LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE equivalence');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
