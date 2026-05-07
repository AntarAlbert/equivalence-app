<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430234144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome ADD pays_id SMALLINT UNSIGNED NOT NULL, DROP pays, CHANGE cadre cadre VARCHAR(255) NOT NULL, CHANGE echelle echelle VARCHAR(255) NOT NULL, CHANGE categorie categorie VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
        $this->addSql('CREATE INDEX IDX_EB4C4D4EA6E44244 ON diplome (pays_id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pays RENAME INDEX alpha2 TO uniq_pays_alpha2');
        $this->addSql('ALTER TABLE pays RENAME INDEX alpha3 TO uniq_pays_alpha3');
        $this->addSql('ALTER TABLE pays RENAME INDEX code_unique TO uniq_pays_code');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EA6E44244');
        $this->addSql('DROP INDEX IDX_EB4C4D4EA6E44244 ON diplome');
        $this->addSql('ALTER TABLE diplome ADD pays VARCHAR(150) NOT NULL, DROP pays_id, CHANGE cadre cadre VARCHAR(10) NOT NULL, CHANGE echelle echelle VARCHAR(10) NOT NULL, CHANGE categorie categorie VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('ALTER TABLE pays RENAME INDEX uniq_pays_alpha2 TO alpha2');
        $this->addSql('ALTER TABLE pays RENAME INDEX uniq_pays_alpha3 TO alpha3');
        $this->addSql('ALTER TABLE pays RENAME INDEX uniq_pays_code TO code_unique');
    }
}
