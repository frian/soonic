<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260219141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename artist.cove_art_path to artist.cover_art_path';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $oldColumnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'artist'
               AND COLUMN_NAME = 'cove_art_path'"
        ) > 0;

        $newColumnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'artist'
               AND COLUMN_NAME = 'cover_art_path'"
        ) > 0;

        if ($oldColumnExists && !$newColumnExists) {
            $this->addSql('ALTER TABLE artist RENAME COLUMN cove_art_path TO cover_art_path');
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on MySQL/MariaDB.'
        );

        $oldColumnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'artist'
               AND COLUMN_NAME = 'cove_art_path'"
        ) > 0;

        $newColumnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'artist'
               AND COLUMN_NAME = 'cover_art_path'"
        ) > 0;

        if ($newColumnExists && !$oldColumnExists) {
            $this->addSql('ALTER TABLE artist RENAME COLUMN cover_art_path TO cove_art_path');
        }
    }
}

