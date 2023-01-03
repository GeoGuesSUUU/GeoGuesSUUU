<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221223100116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country ADD continent VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE game ADD img VARCHAR(255) DEFAULT NULL, CHANGE name title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE level ADD difficulty INT DEFAULT 0, CHANGE description description VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD locale VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP continent');
        $this->addSql('ALTER TABLE game DROP img, CHANGE title name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE level DROP difficulty, CHANGE description description VARCHAR(1024) NOT NULL');
        $this->addSql('ALTER TABLE user DROP locale');
    }
}
