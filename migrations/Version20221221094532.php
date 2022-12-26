<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221221094532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(1024) DEFAULT NULL, CHANGE tags tags VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751AF9C3A25');
        $this->addSql('DROP INDEX IDX_32993751AF9C3A25 ON score');
        $this->addSql('ALTER TABLE score CHANGE levels_id level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_329937515FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_329937515FB14BA7 ON score (level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(1024) NOT NULL, CHANGE tags tags VARCHAR(1024) NOT NULL');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_329937515FB14BA7');
        $this->addSql('DROP INDEX IDX_329937515FB14BA7 ON score');
        $this->addSql('ALTER TABLE score CHANGE level_id levels_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751AF9C3A25 FOREIGN KEY (levels_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_32993751AF9C3A25 ON score (levels_id)');
    }
}
