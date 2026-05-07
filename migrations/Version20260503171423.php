<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503171423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730D2AE8B1E FOREIGN KEY (regle_appliquee_id) REFERENCES regle_equivalence (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592CA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE regle_equivalence ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE regle_equivalence ADD CONSTRAINT FK_78A8018926F859E2 FOREIGN KEY (diplome_id) REFERENCES diplome (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EFF631228');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730D2AE8B1E');
        $this->addSql('ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592CA6E44244');
        $this->addSql('ALTER TABLE regle_equivalence DROP FOREIGN KEY FK_78A8018926F859E2');
        $this->addSql('ALTER TABLE regle_equivalence DROP deleted_at, DROP created_at, DROP updated_at');
    }
}
