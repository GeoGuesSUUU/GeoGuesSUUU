<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119172305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE store_item (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, trending TINYINT(1) NOT NULL, promotion INT NOT NULL, UNIQUE INDEX UNIQ_FDA28BD0126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE store_item ADD CONSTRAINT FK_FDA28BD0126F525E FOREIGN KEY (item_id) REFERENCES item_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE store_item DROP FOREIGN KEY FK_FDA28BD0126F525E');
        $this->addSql('DROP TABLE store_item');
    }
}
