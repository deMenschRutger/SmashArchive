<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171028125315 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant ADD target_entrant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15B91345B7 FOREIGN KEY (target_entrant_id) REFERENCES entrant (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E7BAE15B91345B7 ON entrant (target_entrant_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15B91345B7');
        $this->addSql('DROP INDEX UNIQ_5E7BAE15B91345B7 ON entrant');
        $this->addSql('ALTER TABLE entrant DROP target_entrant_id');
    }
}
