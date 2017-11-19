<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171119131310 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX smashgg_index ON event');
        $this->addSql('ALTER TABLE event ADD external_id VARCHAR(255) DEFAULT NULL, DROP smashgg_id');
        $this->addSql('CREATE INDEX external_id_index ON event (external_id)');
        $this->addSql('DROP INDEX smashgg_index ON entrant');
        $this->addSql('ALTER TABLE entrant ADD external_id VARCHAR(255) DEFAULT NULL, DROP smashgg_id');
        $this->addSql('CREATE INDEX external_id_index ON entrant (external_id)');
        $this->addSql('DROP INDEX smashgg_index ON phase');
        $this->addSql('ALTER TABLE phase ADD external_id VARCHAR(255) DEFAULT NULL, DROP smashgg_id');
        $this->addSql('CREATE INDEX external_id_index ON phase (external_id)');
        $this->addSql('DROP INDEX smashgg_index ON phase_group');
        $this->addSql('UPDATE phase_group SET smashgg_id = original_id WHERE original_id IS NOT NULL');
        $this->addSql('ALTER TABLE phase_group DROP original_id, CHANGE smashgg_id external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX external_id_index ON phase_group (external_id)');
        $this->addSql('DROP INDEX smashgg_index ON phase_group_set');
        $this->addSql('UPDATE phase_group_set SET smashgg_id = original_id WHERE original_id IS NOT NULL');
        $this->addSql('ALTER TABLE phase_group_set DROP original_id, CHANGE smashgg_id external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX external_id_index ON phase_group_set (external_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX external_id_index ON entrant');
        $this->addSql('ALTER TABLE entrant ADD smashgg_id INT DEFAULT NULL, DROP external_id');
        $this->addSql('CREATE INDEX smashgg_index ON entrant (smashgg_id)');
        $this->addSql('DROP INDEX external_id_index ON event');
        $this->addSql('ALTER TABLE event ADD smashgg_id INT DEFAULT NULL, DROP external_id');
        $this->addSql('CREATE INDEX smashgg_index ON event (smashgg_id)');
        $this->addSql('DROP INDEX external_id_index ON phase');
        $this->addSql('ALTER TABLE phase ADD smashgg_id INT DEFAULT NULL, DROP external_id');
        $this->addSql('CREATE INDEX smashgg_index ON phase (smashgg_id)');
        $this->addSql('DROP INDEX external_id_index ON phase_group');
        $this->addSql('ALTER TABLE phase_group ADD original_id INT DEFAULT NULL, CHANGE external_id smashgg_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE INDEX smashgg_index ON phase_group (smashgg_id)');
        $this->addSql('DROP INDEX external_id_index ON phase_group_set');
        $this->addSql('ALTER TABLE phase_group_set ADD original_id INT DEFAULT NULL, CHANGE external_id smashgg_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE INDEX smashgg_index ON phase_group_set (smashgg_id)');
    }
}
