<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114131817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE effect (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, item_type_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, value INT NOT NULL, INDEX IDX_B66091F2F92F3E70 (country_id), INDEX IDX_B66091F2CE11AAC7 (item_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE effect ADD CONSTRAINT FK_B66091F2F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE effect ADD CONSTRAINT FK_B66091F2CE11AAC7 FOREIGN KEY (item_type_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE country DROP effects');
        $this->addSql('ALTER TABLE item_type DROP effects');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_44EE13D25E237E06 ON item_type (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE effect DROP FOREIGN KEY FK_B66091F2F92F3E70');
        $this->addSql('ALTER TABLE effect DROP FOREIGN KEY FK_B66091F2CE11AAC7');
        $this->addSql('DROP TABLE effect');
        $this->addSql('ALTER TABLE country ADD effects LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX UNIQ_44EE13D25E237E06 ON item_type');
        $this->addSql('ALTER TABLE item_type ADD effects LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
