<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171126194622 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE154241827B');
        $this->addSql('DROP INDEX IDX_5E7BAE154241827B ON entrant');
        $this->addSql('ALTER TABLE entrant CHANGE origin_event_id origin_phase_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15AABF7B78 FOREIGN KEY (origin_phase_id) REFERENCES phase (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5E7BAE15AABF7B78 ON entrant (origin_phase_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15AABF7B78');
        $this->addSql('DROP INDEX IDX_5E7BAE15AABF7B78 ON entrant');
        $this->addSql('ALTER TABLE entrant CHANGE origin_phase_id origin_event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE154241827B FOREIGN KEY (origin_event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5E7BAE154241827B ON entrant (origin_event_id)');
    }
}
