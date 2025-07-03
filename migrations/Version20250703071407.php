<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703071407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campus (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, zip_code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hangout (id INT AUTO_INCREMENT NOT NULL, organizer_id INT NOT NULL, status_id INT NOT NULL, spot_id INT NOT NULL, campus_id INT NOT NULL, name VARCHAR(100) NOT NULL, starting_date DATETIME NOT NULL, registration_deadline DATETIME NOT NULL, duration INT NOT NULL, nb_inscriptions_max INT NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_20C5B31E876C4DDA (organizer_id), INDEX IDX_20C5B31E6BF700BD (status_id), INDEX IDX_20C5B31E2DF1D37C (spot_id), INDEX IDX_20C5B31EAF5D55E1 (campus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spot (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, name VARCHAR(100) NOT NULL, address VARCHAR(255) NOT NULL, latitude VARCHAR(255) DEFAULT NULL, longitude VARCHAR(255) DEFAULT NULL, INDEX IDX_B9327A738BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, campus_id INT NOT NULL, pseudo VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, mail VARCHAR(100) NOT NULL, image VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(15) DEFAULT NULL, `admin` TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D6495126AC48 (mail), INDEX IDX_8D93D649AF5D55E1 (campus_id), UNIQUE INDEX UNIQ_IDENTIFIER_PSEUDO (pseudo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_hangout (user_id INT NOT NULL, hangout_id INT NOT NULL, INDEX IDX_78C8AD14A76ED395 (user_id), INDEX IDX_78C8AD14541F802E (hangout_id), PRIMARY KEY(user_id, hangout_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E876C4DDA FOREIGN KEY (organizer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E2DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31EAF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('ALTER TABLE spot ADD CONSTRAINT FK_B9327A738BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14541F802E FOREIGN KEY (hangout_id) REFERENCES hangout (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E876C4DDA');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E6BF700BD');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E2DF1D37C');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31EAF5D55E1');
        $this->addSql('ALTER TABLE spot DROP FOREIGN KEY FK_B9327A738BAC62AF');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649AF5D55E1');
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14A76ED395');
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14541F802E');
        $this->addSql('DROP TABLE campus');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE hangout');
        $this->addSql('DROP TABLE spot');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_hangout');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
