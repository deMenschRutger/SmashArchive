<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171028134041 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15B91345B7');
        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE151AC6034F');
        $this->addSql('DROP INDEX UNIQ_5E7BAE15B91345B7 ON entrant');
        $this->addSql('DROP INDEX IDX_5E7BAE151AC6034F ON entrant');
        $this->addSql('ALTER TABLE entrant ADD origin_event_id INT DEFAULT NULL, ADD parent_entrant_id INT DEFAULT NULL, DROP target_entrant_id, DROP origin_tournament_id');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE154241827B FOREIGN KEY (origin_event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15CCBC1976 FOREIGN KEY (parent_entrant_id) REFERENCES entrant (id)');
        $this->addSql('CREATE INDEX IDX_5E7BAE154241827B ON entrant (origin_event_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E7BAE15CCBC1976 ON entrant (parent_entrant_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE154241827B');
        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15CCBC1976');
        $this->addSql('DROP INDEX IDX_5E7BAE154241827B ON entrant');
        $this->addSql('DROP INDEX UNIQ_5E7BAE15CCBC1976 ON entrant');
        $this->addSql('ALTER TABLE entrant ADD target_entrant_id INT DEFAULT NULL, ADD origin_tournament_id INT DEFAULT NULL, DROP origin_event_id, DROP parent_entrant_id');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15B91345B7 FOREIGN KEY (target_entrant_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE151AC6034F FOREIGN KEY (origin_tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E7BAE15B91345B7 ON entrant (target_entrant_id)');
        $this->addSql('CREATE INDEX IDX_5E7BAE151AC6034F ON entrant (origin_tournament_id)');
    }
}
