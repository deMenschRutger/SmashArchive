<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class Version20180728151407 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username LONGTEXT NOT NULL COMMENT \'(DC2Type:encrypted)\', provider VARCHAR(255) NOT NULL, provider_id LONGTEXT NOT NULL COMMENT \'(DC2Type:encrypted)\', provider_hash VARCHAR(64) NOT NULL COMMENT \'(DC2Type:hashed)\', UNIQUE INDEX UNIQ_1483A5E9343001C4 (provider_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE users');
    }
}
