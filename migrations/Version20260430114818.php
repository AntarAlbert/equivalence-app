<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430114818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE document ADD original_name VARCHAR(255) NOT NULL, ADD type VARCHAR(50) NOT NULL, ADD size INT DEFAULT NULL, ADD mime_type VARCHAR(100) DEFAULT NULL, ADD uploaded_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE equivalence_id equivalence_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE document DROP original_name, DROP type, DROP size, DROP mime_type, DROP uploaded_at, DROP updated_at, CHANGE equivalence_id equivalence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equivalence DROP updated_at');
    }
}
