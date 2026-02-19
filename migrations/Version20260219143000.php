<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on language.code';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $duplicateCodes = (int) $this->connection->fetchOne(
            <<<'SQL'
SELECT COUNT(*)
FROM (
    SELECT code
    FROM language
    GROUP BY code
    HAVING COUNT(*) > 1
) t
SQL
        );
        $this->abortIf($duplicateCodes > 0, 'Cannot add unique index on language.code: duplicate values exist.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_LANGUAGE_CODE ON language (code)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $this->addSql('DROP INDEX UNIQ_LANGUAGE_CODE ON language');
    }
}

