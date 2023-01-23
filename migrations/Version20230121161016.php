<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230121161016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country CHANGE init_life init_life BIGINT UNSIGNED NOT NULL, CHANGE init_price init_price INT UNSIGNED NOT NULL, CHANGE life life BIGINT UNSIGNED NOT NULL, CHANGE life_max life_max BIGINT UNSIGNED NOT NULL, CHANGE shield shield BIGINT UNSIGNED NOT NULL, CHANGE shield_max shield_max BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE item_type ADD price INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country CHANGE init_life init_life BIGINT NOT NULL, CHANGE life life BIGINT NOT NULL, CHANGE life_max life_max BIGINT NOT NULL, CHANGE shield shield BIGINT NOT NULL, CHANGE shield_max shield_max BIGINT NOT NULL, CHANGE init_price init_price INT NOT NULL');
        $this->addSql('ALTER TABLE item_type DROP price');
    }
}
