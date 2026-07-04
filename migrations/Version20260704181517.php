<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704181517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arrete (id INT AUTO_INCREMENT NOT NULL, numero_arrete VARCHAR(50) NOT NULL, date_arrete DATE NOT NULL, titre VARCHAR(255) NOT NULL, article_dispositif LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, equivalence_id INT NOT NULL, UNIQUE INDEX UNIQ_8D9860A501E9530 (numero_arrete), UNIQUE INDEX UNIQ_8D9860ABEC6ADF1 (equivalence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE arrete_considerant (id INT AUTO_INCREMENT NOT NULL, ordre INT NOT NULL, arrete_id INT NOT NULL, considerant_id INT NOT NULL, INDEX IDX_FF8DF7FEF9001553 (arrete_id), INDEX IDX_FF8DF7FEF951F94F (considerant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE considerant (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, reference VARCHAR(50) NOT NULL, date DATE DEFAULT NULL, portant VARCHAR(255) NOT NULL, extrait LONGTEXT DEFAULT NULL, ordre INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE diplome (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, domaine VARCHAR(100) DEFAULT NULL, niveau VARCHAR(50) DEFAULT NULL, duree VARCHAR(20) DEFAULT NULL, validation_status VARCHAR(20) DEFAULT \'pending\' NOT NULL, approved_at DATETIME DEFAULT NULL, rejected_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, organisme VARCHAR(255) DEFAULT NULL, etablissement_id INT DEFAULT NULL, proposed_by_id INT DEFAULT NULL, etablissement_source_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, approved_by_id INT DEFAULT NULL, rejected_by_id INT DEFAULT NULL, INDEX IDX_EB4C4D4EFF631228 (etablissement_id), INDEX IDX_EB4C4D4EDAB5A938 (proposed_by_id), INDEX IDX_EB4C4D4EF8E39108 (etablissement_source_id), INDEX IDX_EB4C4D4EB03A8386 (created_by_id), INDEX IDX_EB4C4D4E896DBBDE (updated_by_id), INDEX IDX_EB4C4D4E2D234F6A (approved_by_id), INDEX IDX_EB4C4D4ECBF05FC9 (rejected_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE diplome_obtenu (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, nom_normalise VARCHAR(100) DEFAULT NULL, prenom_normalise VARCHAR(100) DEFAULT NULL, numero_cni VARCHAR(50) DEFAULT NULL, annee_obtention VARCHAR(20) DEFAULT NULL, numero_diplome VARCHAR(50) DEFAULT NULL, mention VARCHAR(255) DEFAULT NULL, moyenne DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, diplome_id INT NOT NULL, soumis_par_id INT NOT NULL, INDEX IDX_187E46FF26F859E2 (diplome_id), INDEX IDX_187E46FF924CB1B (soumis_par_id), INDEX IDX_187E46FF4F97B509 (numero_cni), INDEX IDX_187E46FF8B610943116C3DF4 (nom_normalise, prenom_normalise), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, size INT DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, uploaded_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, equivalence_id INT NOT NULL, INDEX IDX_D8698A76BEC6ADF1 (equivalence_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE equivalence (id INT AUTO_INCREMENT NOT NULL, numero_dossier VARCHAR(20) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, date_naissance DATE DEFAULT NULL, lieu_naissance VARCHAR(150) DEFAULT NULL, email VARCHAR(180) NOT NULL, cni VARCHAR(12) DEFAULT NULL, cni_date_delivrance DATE DEFAULT NULL, cni_lieu_delivrance VARCHAR(150) DEFAULT NULL, cni_date_duplicata DATE DEFAULT NULL, cni_lieu_duplicata VARCHAR(150) DEFAULT NULL, emploi VARCHAR(30) DEFAULT NULL, matricule VARCHAR(50) DEFAULT NULL, diplome VARCHAR(255) DEFAULT NULL, universite VARCHAR(255) DEFAULT NULL, pays VARCHAR(100) DEFAULT NULL, observation LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, decision VARCHAR(50) DEFAULT NULL, classement VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, nationalite_id SMALLINT UNSIGNED DEFAULT NULL, diplome_reference_id INT NOT NULL, regle_appliquee_id INT DEFAULT NULL, INDEX IDX_B74B730A76ED395 (user_id), INDEX IDX_B74B7301B063272 (nationalite_id), INDEX IDX_B74B730B6E9EAAB (diplome_reference_id), INDEX IDX_B74B730D2AE8B1E (regle_appliquee_id), UNIQUE INDEX UNIQ_NUMERO_DOSSIER (numero_dossier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(150) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, pays_id SMALLINT UNSIGNED DEFAULT NULL, INDEX IDX_20FD592CA6E44244 (pays_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE pays (id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, code INT NOT NULL, alpha2 VARCHAR(2) NOT NULL, alpha3 VARCHAR(3) NOT NULL, nom_en_gb VARCHAR(45) NOT NULL, nom_fr_fr VARCHAR(45) NOT NULL, UNIQUE INDEX uniq_pays_alpha2 (alpha2), UNIQUE INDEX uniq_pays_alpha3 (alpha3), UNIQUE INDEX uniq_pays_code (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE regle_equivalence (id INT AUTO_INCREMENT NOT NULL, cadre VARCHAR(255) NOT NULL, echelle VARCHAR(255) NOT NULL, categorie VARCHAR(10) NOT NULL, bonification INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, actif TINYINT NOT NULL, texte_reference LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, diplome_id INT NOT NULL, INDEX IDX_78A8018926F859E2 (diplome_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, verified_at DATETIME DEFAULT NULL, nom VARCHAR(150) DEFAULT NULL, prenom VARCHAR(150) DEFAULT NULL, date_naissance DATE DEFAULT NULL, lieu_naissance VARCHAR(255) DEFAULT NULL, cni VARCHAR(100) DEFAULT NULL, cni_date_delivrance DATE DEFAULT NULL, cni_lieu_delivrance VARCHAR(255) DEFAULT NULL, cni_date_duplicata DATE DEFAULT NULL, cni_lieu_duplicata VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, full_name VARCHAR(255) DEFAULT NULL, nationalite_id SMALLINT UNSIGNED DEFAULT NULL, etablissement_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6491B063272 (nationalite_id), INDEX IDX_8D93D649FF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
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
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B7301B063272 FOREIGN KEY (nationalite_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730B6E9EAAB FOREIGN KEY (diplome_reference_id) REFERENCES diplome (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE equivalence ADD CONSTRAINT FK_B74B730D2AE8B1E FOREIGN KEY (regle_appliquee_id) REFERENCES regle_equivalence (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592CA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE regle_equivalence ADD CONSTRAINT FK_78A8018926F859E2 FOREIGN KEY (diplome_id) REFERENCES diplome (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6491B063272 FOREIGN KEY (nationalite_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
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
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730A76ED395');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B7301B063272');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730B6E9EAAB');
        $this->addSql('ALTER TABLE equivalence DROP FOREIGN KEY FK_B74B730D2AE8B1E');
        $this->addSql('ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592CA6E44244');
        $this->addSql('ALTER TABLE regle_equivalence DROP FOREIGN KEY FK_78A8018926F859E2');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6491B063272');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649FF631228');
        $this->addSql('DROP TABLE arrete');
        $this->addSql('DROP TABLE arrete_considerant');
        $this->addSql('DROP TABLE considerant');
        $this->addSql('DROP TABLE diplome');
        $this->addSql('DROP TABLE diplome_obtenu');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE equivalence');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE regle_equivalence');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
