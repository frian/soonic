<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique indexes on radio.name and radio.stream_url';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $duplicateNames = (int) $this->connection->fetchOne(
            <<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT name
    FROM radio
    GROUP BY name
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateNames > 0, 'Cannot add unique index on radio.name: duplicate values exist.');

        $duplicateStreamUrls = (int) $this->connection->fetchOne(
            <<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT stream_url
    FROM radio
    GROUP BY stream_url
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateStreamUrls > 0, 'Cannot add unique index on radio.stream_url: duplicate values exist.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_RADIO_NAME ON radio (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_RADIO_STREAM_URL ON radio (stream_url)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('DROP INDEX UNIQ_RADIO_NAME ON radio');
        $this->addSql('DROP INDEX UNIQ_RADIO_STREAM_URL ON radio');
    }
}

