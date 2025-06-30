<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630140718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_hangout (user_id INT NOT NULL, hangout_id INT NOT NULL, INDEX IDX_78C8AD14A76ED395 (user_id), INDEX IDX_78C8AD14541F802E (hangout_id), PRIMARY KEY(user_id, hangout_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_hangout ADD CONSTRAINT FK_78C8AD14541F802E FOREIGN KEY (hangout_id) REFERENCES hangout (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hangout ADD organizer_id INT NOT NULL, ADD status_id INT NOT NULL, ADD spot_id INT NOT NULL, ADD campus_id INT NOT NULL');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E876C4DDA FOREIGN KEY (organizer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31E2DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id)');
        $this->addSql('ALTER TABLE hangout ADD CONSTRAINT FK_20C5B31EAF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E876C4DDA ON hangout (organizer_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E6BF700BD ON hangout (status_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31E2DF1D37C ON hangout (spot_id)');
        $this->addSql('CREATE INDEX IDX_20C5B31EAF5D55E1 ON hangout (campus_id)');
        $this->addSql('ALTER TABLE spot ADD city_id INT NOT NULL');
        $this->addSql('ALTER TABLE spot ADD CONSTRAINT FK_B9327A738BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_B9327A738BAC62AF ON spot (city_id)');
        $this->addSql('ALTER TABLE user ADD campus_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495126AC48 ON user (mail)');
        $this->addSql('CREATE INDEX IDX_8D93D649AF5D55E1 ON user (campus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14A76ED395');
        $this->addSql('ALTER TABLE user_hangout DROP FOREIGN KEY FK_78C8AD14541F802E');
        $this->addSql('DROP TABLE user_hangout');
        $this->addSql('ALTER TABLE spot DROP FOREIGN KEY FK_B9327A738BAC62AF');
        $this->addSql('DROP INDEX IDX_B9327A738BAC62AF ON spot');
        $this->addSql('ALTER TABLE spot DROP city_id');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E876C4DDA');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E6BF700BD');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31E2DF1D37C');
        $this->addSql('ALTER TABLE hangout DROP FOREIGN KEY FK_20C5B31EAF5D55E1');
        $this->addSql('DROP INDEX IDX_20C5B31E876C4DDA ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31E6BF700BD ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31E2DF1D37C ON hangout');
        $this->addSql('DROP INDEX IDX_20C5B31EAF5D55E1 ON hangout');
        $this->addSql('ALTER TABLE hangout DROP organizer_id, DROP status_id, DROP spot_id, DROP campus_id');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649AF5D55E1');
        $this->addSql('DROP INDEX UNIQ_8D93D6495126AC48 ON `user`');
        $this->addSql('DROP INDEX IDX_8D93D649AF5D55E1 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP campus_id');
    }
}
