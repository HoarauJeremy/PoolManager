<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022064913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS intervention_materiel (intervention_id INT NOT NULL, materiel_id INT NOT NULL, INDEX IDX_2541CB328EAE3863 (intervention_id), INDEX IDX_2541CB3216880AAF (materiel_id), PRIMARY KEY(intervention_id, materiel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS materiel (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, quantite INT NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE intervention_materiel ADD CONSTRAINT FK_2541CB328EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_materiel ADD CONSTRAINT FK_2541CB3216880AAF FOREIGN KEY (materiel_id) REFERENCES materiel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F8EAE3863');
        $this->addSql('ALTER TABLE intervention_material DROP FOREIGN KEY FK_412D0E368EAE3863');
        $this->addSql('ALTER TABLE intervention_material DROP FOREIGN KEY FK_412D0E36E308AC6F');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE intervention_material');
        $this->addSql('DROP TABLE material');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS image (id INT AUTO_INCREMENT NOT NULL, intervention_id INT DEFAULT NULL, lien VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C53D045F8EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE IF NOT EXISTS intervention_material (intervention_id INT NOT NULL, material_id INT NOT NULL, INDEX IDX_412D0E368EAE3863 (intervention_id), INDEX IDX_412D0E36E308AC6F (material_id), PRIMARY KEY(intervention_id, material_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE IF NOT EXISTS material (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, quantite INT NOT NULL, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE intervention_material ADD CONSTRAINT FK_412D0E368EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_material ADD CONSTRAINT FK_412D0E36E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_materiel DROP FOREIGN KEY FK_2541CB328EAE3863');
        $this->addSql('ALTER TABLE intervention_materiel DROP FOREIGN KEY FK_2541CB3216880AAF');
        $this->addSql('DROP TABLE intervention_materiel');
        $this->addSql('DROP TABLE materiel');
    }
}
