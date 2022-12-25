<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221225180811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country ADD code VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE game ADD server VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE country ADD init_life INT NOT NULL, ADD owned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE level CHANGE difficulty difficulty INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP code');
        $this->addSql('ALTER TABLE game DROP server');
        $this->addSql('ALTER TABLE country DROP init_life, DROP owned_at');
        $this->addSql('ALTER TABLE level CHANGE difficulty difficulty INT DEFAULT 0');
    }
}
