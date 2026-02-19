<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add search indexes and fulltext indexes for song, album and artist names';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('CREATE INDEX IDX_SONG_TITLE ON song (title)');
        $this->addSql('CREATE INDEX IDX_ALBUM_NAME ON album (name)');
        $this->addSql('CREATE INDEX IDX_ARTIST_NAME ON artist (name)');

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

        $this->addSql('DROP INDEX FT_SONG_TITLE ON song');
        $this->addSql('DROP INDEX FT_ALBUM_NAME ON album');
        $this->addSql('DROP INDEX FT_ARTIST_NAME ON artist');

        $this->addSql('DROP INDEX IDX_SONG_TITLE ON song');
        $this->addSql('DROP INDEX IDX_ALBUM_NAME ON album');
        $this->addSql('DROP INDEX IDX_ARTIST_NAME ON artist');
    }
}
