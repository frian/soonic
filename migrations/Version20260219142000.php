<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219142000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on artist.artist_slug';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $duplicateSlugs = (int) $this->connection->fetchOne(
            <<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT artist_slug
    FROM artist
    GROUP BY artist_slug
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateSlugs > 0, 'Cannot add unique index on artist.artist_slug: duplicate values exist.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_ARTIST_SLUG ON artist (artist_slug)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('DROP INDEX UNIQ_ARTIST_SLUG ON artist');
    }
}

