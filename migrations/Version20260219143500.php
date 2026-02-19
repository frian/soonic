<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219143500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on theme.name';
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
    FROM theme
    GROUP BY name
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateNames > 0, 'Cannot add unique index on theme.name: duplicate values exist.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_THEME_NAME ON theme (name)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('DROP INDEX UNIQ_THEME_NAME ON theme');
    }
}

