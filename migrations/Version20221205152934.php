<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205152934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, flag VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, flag VARCHAR(255) NOT NULL, INDEX IDX_5373C966A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country_item (country_id INT NOT NULL, item_type_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_9828AEFAF92F3E70 (country_id), INDEX IDX_9828AEFACE11AAC7 (item_type_id), PRIMARY KEY(country_id, item_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, tags VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, type VARCHAR(255) NOT NULL, rarity VARCHAR(255) NOT NULL, fantastic TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, INDEX IDX_9AEACC13E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, flag VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, levels_id INT DEFAULT NULL, user_id INT DEFAULT NULL, score INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', time INT NOT NULL, INDEX IDX_32993751AF9C3A25 (levels_id), INDEX IDX_32993751A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, coins INT NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_item (item_type_id INT NOT NULL, user_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_659A69D7CE11AAC7 (item_type_id), INDEX IDX_659A69D7A76ED395 (user_id), PRIMARY KEY(item_type_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE country ADD CONSTRAINT FK_5373C966A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE country_item ADD CONSTRAINT FK_9828AEFAF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE country_item ADD CONSTRAINT FK_9828AEFACE11AAC7 FOREIGN KEY (item_type_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751AF9C3A25 FOREIGN KEY (levels_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_item ADD CONSTRAINT FK_659A69D7CE11AAC7 FOREIGN KEY (item_type_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE user_item ADD CONSTRAINT FK_659A69D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP FOREIGN KEY FK_5373C966A76ED395');
        $this->addSql('ALTER TABLE country_item DROP FOREIGN KEY FK_9828AEFAF92F3E70');
        $this->addSql('ALTER TABLE country_item DROP FOREIGN KEY FK_9828AEFACE11AAC7');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13E48FD905');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751AF9C3A25');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('ALTER TABLE user_item DROP FOREIGN KEY FK_659A69D7CE11AAC7');
        $this->addSql('ALTER TABLE user_item DROP FOREIGN KEY FK_659A69D7A76ED395');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE country_item');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE item_type');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_item');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
