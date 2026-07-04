<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260513083748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arrete ADD CONSTRAINT FK_8D9860ABEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE arrete_considerant ADD CONSTRAINT FK_FF8DF7FEF9001553 FOREIGN KEY (arrete_id) REFERENCES arrete (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE arrete_considerant ADD CONSTRAINT FK_FF8DF7FEF951F94F FOREIGN KEY (considerant_id) REFERENCES considerant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EDAB5A938 FOREIGN KEY (proposed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EF8E39108 FOREIGN KEY (etablissement_source_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4EB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4E2D234F6A FOREIGN KEY (approved_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4ECBF05FC9 FOREIGN KEY (rejected_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE diplome_obtenu ADD CONSTRAINT FK_187E46FF26F859E2 FOREIGN KEY (diplome_id) REFERENCES diplome (id)');
        $this->addSql('ALTER TABLE diplome_obtenu ADD CONSTRAINT FK_187E46FF924CB1B FOREIGN KEY (soumis_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BEC6ADF1 FOREIGN KEY (equivalence_id) REFERENCES equivalence (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B7301B063272 FOREIGN KEY (nationalite_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730D2AE8B1E FOREIGN KEY (regle_appliquee_id) REFERENCES regle_equivalence (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592CA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE regle_equivalence ADD CONSTRAINT FK_78A8018926F859E2 FOREIGN KEY (diplome_id) REFERENCES diplome (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(150) DEFAULT NULL, ADD prenom VARCHAR(150) DEFAULT NULL, ADD date_naissance DATE DEFAULT NULL, ADD cni VARCHAR(100) DEFAULT NULL, ADD cni_date_delivrance DATE DEFAULT NULL, ADD cni_lieu_delivrance VARCHAR(255) DEFAULT NULL, ADD cni_date_duplicata DATE DEFAULT NULL, ADD cni_lieu_duplicata VARCHAR(255) DEFAULT NULL, ADD nationalite_id SMALLINT UNSIGNED DEFAULT NULL, CHANGE full_name lieu_naissance VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491B063272 FOREIGN KEY (nationalite_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491B063272 ON user (nationalite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arrete DROP FOREIGN KEY FK_8D9860ABEC6ADF1');
        $this->addSql('ALTER TABLE arrete_considerant DROP FOREIGN KEY FK_FF8DF7FEF9001553');
        $this->addSql('ALTER TABLE arrete_considerant DROP FOREIGN KEY FK_FF8DF7FEF951F94F');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EFF631228');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EDAB5A938');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EF8E39108');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4EB03A8386');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4E896DBBDE');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4E2D234F6A');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4ECBF05FC9');
        $this->addSql('ALTER TABLE diplome_obtenu DROP FOREIGN KEY FK_187E46FF26F859E2');
        $this->addSql('ALTER TABLE diplome_obtenu DROP FOREIGN KEY FK_187E46FF924CB1B');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BEC6ADF1');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B7301B063272');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730D2AE8B1E');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730A76ED395');
        $this->addSql('ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592CA6E44244');
        $this->addSql('ALTER TABLE regle_equivalence DROP FOREIGN KEY FK_78A8018926F859E2');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6491B063272');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649FF631228');
        $this->addSql('DROP INDEX IDX_8D93D6491B063272 ON `user`');
        $this->addSql('ALTER TABLE `user` ADD full_name VARCHAR(255) DEFAULT NULL, DROP nom, DROP prenom, DROP date_naissance, DROP lieu_naissance, DROP cni, DROP cni_date_delivrance, DROP cni_lieu_delivrance, DROP cni_date_duplicata, DROP cni_lieu_duplicata, DROP nationalite_id');
    }
}
