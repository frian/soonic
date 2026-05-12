<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial schema (tables and foreign keys) for Soonic';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $albumTableExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'album'"
        ) > 0;

        if ($albumTableExists) {
            // Existing installations already have the schema.
            return;
        }

        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, album_slug VARCHAR(255) NOT NULL, song_count INT NOT NULL, duration VARCHAR(8) NOT NULL, year SMALLINT DEFAULT NULL, genre VARCHAR(255) DEFAULT NULL, path VARCHAR(1024) NOT NULL, cover_art_path VARCHAR(1024) DEFAULT NULL, INDEX IDX_ALBUM_NAME (name), INDEX IDX_ALBUM_SLUG (album_slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE artist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, artist_slug VARCHAR(255) NOT NULL, album_count SMALLINT NOT NULL, cover_art_path VARCHAR(1024) DEFAULT NULL, UNIQUE INDEX UNIQ_ARTIST_SLUG (artist_slug), INDEX IDX_ARTIST_NAME (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE artist_album (artist_id INT NOT NULL, album_id INT NOT NULL, INDEX IDX_59945E10B7970CF8 (artist_id), INDEX IDX_59945E101137ABCF (album_id), PRIMARY KEY (artist_id, album_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE config (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, theme_id INT NOT NULL, UNIQUE INDEX UNIQ_D48A2F7C82F1BAF4 (language_id), UNIQUE INDEX UNIQ_D48A2F7C59027487 (theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(8) NOT NULL, UNIQUE INDEX UNIQ_LANGUAGE_CODE (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE radio (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, stream_url VARCHAR(512) NOT NULL, homepage_url VARCHAR(512) DEFAULT NULL, UNIQUE INDEX UNIQ_RADIO_NAME (name), UNIQUE INDEX UNIQ_RADIO_STREAM_URL (stream_url), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE song (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(1024) NOT NULL, web_path VARCHAR(1024) NOT NULL, title VARCHAR(512) NOT NULL, track_number SMALLINT NOT NULL, year SMALLINT DEFAULT NULL, genre VARCHAR(64) DEFAULT NULL, duration VARCHAR(8) NOT NULL, album_id INT NOT NULL, artist_id INT NOT NULL, UNIQUE INDEX UNIQ_SONG_WEB_PATH (web_path), INDEX IDX_33EDEEA11137ABCF (album_id), INDEX IDX_33EDEEA1B7970CF8 (artist_id), INDEX IDX_SONG_TITLE (title), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_THEME_NAME (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE artist_album ADD CONSTRAINT FK_59945E10B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_album ADD CONSTRAINT FK_59945E101137ABCF FOREIGN KEY (album_id) REFERENCES album (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7C82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7C59027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA11137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('CREATE FULLTEXT INDEX FT_SONG_TITLE ON song (title)');
        $this->addSql('CREATE FULLTEXT INDEX FT_ALBUM_NAME ON album (name)');
        $this->addSql('CREATE FULLTEXT INDEX FT_ARTIST_NAME ON artist (name)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $albumTableExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'album'"
        ) > 0;

        if (!$albumTableExists) {
            return;
        }

        $this->addSql('ALTER TABLE artist_album DROP FOREIGN KEY FK_59945E10B7970CF8');
        $this->addSql('ALTER TABLE artist_album DROP FOREIGN KEY FK_59945E101137ABCF');
        $this->addSql('ALTER TABLE config DROP FOREIGN KEY FK_D48A2F7C82F1BAF4');
        $this->addSql('ALTER TABLE config DROP FOREIGN KEY FK_D48A2F7C59027487');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA11137ABCF');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1B7970CF8');
        $this->addSql('DROP TABLE artist_album');
        $this->addSql('DROP TABLE song');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE radio');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE theme');
    }
}
