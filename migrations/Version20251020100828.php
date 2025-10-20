<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020100828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, code_postal INT NOT NULL, tel_fixe INT DEFAULT NULL, tel_gsm INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, intervention_id INT DEFAULT NULL, lien VARCHAR(255) NOT NULL, INDEX IDX_C53D045F8EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, client_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_fin DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', adresse VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, code_postal INT NOT NULL, infos LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_D11814ABC54C8C93 (type_id), INDEX IDX_D11814AB19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention_material (intervention_id INT NOT NULL, material_id INT NOT NULL, INDEX IDX_412D0E368EAE3863 (intervention_id), INDEX IDX_412D0E36E308AC6F (material_id), PRIMARY KEY(intervention_id, material_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention_user (intervention_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_822CCE8B8EAE3863 (intervention_id), INDEX IDX_822CCE8BA76ED395 (user_id), PRIMARY KEY(intervention_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, quantite INT NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_intervention (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, tel_gsm VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABC54C8C93 FOREIGN KEY (type_id) REFERENCES type_intervention (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE intervention_material ADD CONSTRAINT FK_412D0E368EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_material ADD CONSTRAINT FK_412D0E36E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_user ADD CONSTRAINT FK_822CCE8B8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_user ADD CONSTRAINT FK_822CCE8BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F8EAE3863');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABC54C8C93');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB19EB6921');
        $this->addSql('ALTER TABLE intervention_material DROP FOREIGN KEY FK_412D0E368EAE3863');
        $this->addSql('ALTER TABLE intervention_material DROP FOREIGN KEY FK_412D0E36E308AC6F');
        $this->addSql('ALTER TABLE intervention_user DROP FOREIGN KEY FK_822CCE8B8EAE3863');
        $this->addSql('ALTER TABLE intervention_user DROP FOREIGN KEY FK_822CCE8BA76ED395');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE intervention_material');
        $this->addSql('DROP TABLE intervention_user');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE type_intervention');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
