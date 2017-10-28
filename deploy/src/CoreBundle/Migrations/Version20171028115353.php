<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171028115353 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant ADD origin_tournament_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE151AC6034F FOREIGN KEY (origin_tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5E7BAE151AC6034F ON entrant (origin_tournament_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE151AC6034F');
        $this->addSql('DROP INDEX IDX_5E7BAE151AC6034F ON entrant');
        $this->addSql('ALTER TABLE entrant DROP origin_tournament_id');
    }
}
