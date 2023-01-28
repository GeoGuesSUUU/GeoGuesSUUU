<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230128132015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE store_item DROP FOREIGN KEY FK_FDA28BD0126F525E');
        $this->addSql('DROP INDEX UNIQ_FDA28BD0126F525E ON store_item');
        $this->addSql('ALTER TABLE store_item CHANGE item_id item_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE store_item ADD CONSTRAINT FK_FDA28BD0CE11AAC7 FOREIGN KEY (item_type_id) REFERENCES item_type (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FDA28BD0CE11AAC7 ON store_item (item_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE store_item DROP FOREIGN KEY FK_FDA28BD0CE11AAC7');
        $this->addSql('DROP INDEX UNIQ_FDA28BD0CE11AAC7 ON store_item');
        $this->addSql('ALTER TABLE store_item CHANGE item_type_id item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE store_item ADD CONSTRAINT FK_FDA28BD0126F525E FOREIGN KEY (item_id) REFERENCES item_type (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FDA28BD0126F525E ON store_item (item_id)');
    }
}
