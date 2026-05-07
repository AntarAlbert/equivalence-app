<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430230348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence CHANGE diplome_reference_id diplome_reference_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('ALTER TABLE equivalence CHANGE diplome_reference_id diplome_reference_id INT NOT NULL');
    }
}
