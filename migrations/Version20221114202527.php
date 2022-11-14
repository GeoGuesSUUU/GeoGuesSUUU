<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221114202527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, tags VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, type VARCHAR(255) NOT NULL, rarity INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_user (item_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_45A392B2126F525E (item_id), INDEX IDX_45A392B2A76ED395 (user_id), PRIMARY KEY(item_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_game (level_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_4A0ECA9D5FB14BA7 (level_id), INDEX IDX_4A0ECA9DE48FD905 (game_id), PRIMARY KEY(level_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, levels_id INT DEFAULT NULL, user_id INT DEFAULT NULL, score INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_32993751AF9C3A25 (levels_id), INDEX IDX_32993751A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_user ADD CONSTRAINT FK_45A392B2126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_user ADD CONSTRAINT FK_45A392B2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_game ADD CONSTRAINT FK_4A0ECA9D5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_game ADD CONSTRAINT FK_4A0ECA9DE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751AF9C3A25 FOREIGN KEY (levels_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user ADD coins INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_user DROP FOREIGN KEY FK_45A392B2126F525E');
        $this->addSql('ALTER TABLE item_user DROP FOREIGN KEY FK_45A392B2A76ED395');
        $this->addSql('ALTER TABLE level_game DROP FOREIGN KEY FK_4A0ECA9D5FB14BA7');
        $this->addSql('ALTER TABLE level_game DROP FOREIGN KEY FK_4A0ECA9DE48FD905');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751AF9C3A25');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE item_user');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE level_game');
        $this->addSql('DROP TABLE score');
        $this->addSql('ALTER TABLE `user` DROP coins');
    }
}
