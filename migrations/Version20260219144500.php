<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219144500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on song.web_path';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $duplicateWebPaths = (int) $this->connection->fetchOne(
            <<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT web_path
    FROM song
    GROUP BY web_path
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateWebPaths > 0, 'Cannot add unique index on song.web_path: duplicate values exist.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_SONG_WEB_PATH ON song (web_path)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('DROP INDEX UNIQ_SONG_WEB_PATH ON song');
    }
}

