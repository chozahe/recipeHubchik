<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130123407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, rating, comment, created_at, updated_at, user_id FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating SMALLINT NOT NULL, comment VARCHAR(1000) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, recipe_id INTEGER NOT NULL, CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C659D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, rating, comment, created_at, updated_at, user_id) SELECT id, rating, comment, created_at, updated_at, user_id FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('CREATE INDEX IDX_794381C659D8A214 ON review (recipe_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, name, password, created_at, is_admin FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_admin BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, name, password, created_at, is_admin) SELECT id, email, name, password, created_at, is_admin FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, rating, comment, created_at, updated_at, user_id FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating SMALLINT NOT NULL, comment VARCHAR(1000) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, rating, comment, created_at, updated_at, user_id) SELECT id, rating, comment, created_at, updated_at, user_id FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, name, password, created_at, is_admin FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_admin BOOLEAN DEFAULT FALSE NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, name, password, created_at, is_admin) SELECT id, email, name, password, created_at, is_admin FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
    }
}
