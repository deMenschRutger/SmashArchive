<?php

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170513145645 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX rank_index ON result (rank)');
        $this->addSql('CREATE INDEX smashgg_index ON player (smashgg_id)');
        $this->addSql('CREATE INDEX slug_index ON player (slug)');
        $this->addSql('CREATE INDEX gamer_tag_index ON player (gamer_tag)');
        $this->addSql('CREATE INDEX name_index ON player (name)');
        $this->addSql('CREATE INDEX region_index ON player (region)');
        $this->addSql('CREATE INDEX city_index ON player (city)');
        $this->addSql('CREATE INDEX is_competing_index ON player (is_competing)');
        $this->addSql('CREATE INDEX is_active_index ON player (is_active)');
        $this->addSql('CREATE INDEX created_at_index ON player (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON player (updated_at)');
        $this->addSql('CREATE INDEX smashgg_index ON event (smashgg_id)');
        $this->addSql('CREATE INDEX name_index ON event (name)');
        $this->addSql('CREATE INDEX created_at_index ON event (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON event (updated_at)');
        $this->addSql('CREATE INDEX smashgg_index ON entrant (smashgg_id)');
        $this->addSql('CREATE INDEX name_index ON entrant (name)');
        $this->addSql('CREATE INDEX created_at_index ON entrant (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON entrant (updated_at)');
        $this->addSql('CREATE INDEX name_index ON country (name)');
        $this->addSql('ALTER TABLE phase CHANGE phaseorder phase_order INT NOT NULL');
        $this->addSql('CREATE INDEX smashgg_index ON phase (smashgg_id)');
        $this->addSql('CREATE INDEX name_index ON phase (name)');
        $this->addSql('CREATE INDEX phase_order_index ON phase (phase_order)');
        $this->addSql('CREATE INDEX created_at_index ON phase (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON phase (updated_at)');
        $this->addSql('CREATE INDEX smashgg_index ON game (smashgg_id)');
        $this->addSql('CREATE INDEX name_index ON game (name)');
        $this->addSql('CREATE INDEX created_at_index ON game (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON game (updated_at)');
        $this->addSql('CREATE INDEX smashgg_index ON phase_group (smashgg_id)');
        $this->addSql('CREATE INDEX name_index ON phase_group (name)');
        $this->addSql('CREATE INDEX type_index ON phase_group (type)');
        $this->addSql('CREATE INDEX created_at_index ON phase_group (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON phase_group (updated_at)');
        $this->addSql('CREATE INDEX smashgg_index ON phase_group_set (smashgg_id)');
        $this->addSql('CREATE INDEX round_index ON phase_group_set (round)');
        $this->addSql('CREATE INDEX is_ranked_index ON phase_group_set (is_ranked)');
        $this->addSql('CREATE INDEX created_at_index ON phase_group_set (created_at)');
        $this->addSql('CREATE INDEX updated_at_index ON phase_group_set (updated_at)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX name_index ON country');
        $this->addSql('DROP INDEX smashgg_index ON entrant');
        $this->addSql('DROP INDEX name_index ON entrant');
        $this->addSql('DROP INDEX created_at_index ON entrant');
        $this->addSql('DROP INDEX updated_at_index ON entrant');
        $this->addSql('DROP INDEX smashgg_index ON event');
        $this->addSql('DROP INDEX name_index ON event');
        $this->addSql('DROP INDEX created_at_index ON event');
        $this->addSql('DROP INDEX updated_at_index ON event');
        $this->addSql('DROP INDEX smashgg_index ON game');
        $this->addSql('DROP INDEX name_index ON game');
        $this->addSql('DROP INDEX created_at_index ON game');
        $this->addSql('DROP INDEX updated_at_index ON game');
        $this->addSql('DROP INDEX smashgg_index ON phase');
        $this->addSql('DROP INDEX name_index ON phase');
        $this->addSql('DROP INDEX phase_order_index ON phase');
        $this->addSql('DROP INDEX created_at_index ON phase');
        $this->addSql('DROP INDEX updated_at_index ON phase');
        $this->addSql('ALTER TABLE phase CHANGE phase_order phaseOrder INT NOT NULL');
        $this->addSql('DROP INDEX smashgg_index ON phase_group');
        $this->addSql('DROP INDEX name_index ON phase_group');
        $this->addSql('DROP INDEX type_index ON phase_group');
        $this->addSql('DROP INDEX created_at_index ON phase_group');
        $this->addSql('DROP INDEX updated_at_index ON phase_group');
        $this->addSql('DROP INDEX smashgg_index ON phase_group_set');
        $this->addSql('DROP INDEX round_index ON phase_group_set');
        $this->addSql('DROP INDEX is_ranked_index ON phase_group_set');
        $this->addSql('DROP INDEX created_at_index ON phase_group_set');
        $this->addSql('DROP INDEX updated_at_index ON phase_group_set');
        $this->addSql('DROP INDEX smashgg_index ON player');
        $this->addSql('DROP INDEX slug_index ON player');
        $this->addSql('DROP INDEX gamer_tag_index ON player');
        $this->addSql('DROP INDEX name_index ON player');
        $this->addSql('DROP INDEX region_index ON player');
        $this->addSql('DROP INDEX city_index ON player');
        $this->addSql('DROP INDEX is_competing_index ON player');
        $this->addSql('DROP INDEX is_active_index ON player');
        $this->addSql('DROP INDEX created_at_index ON player');
        $this->addSql('DROP INDEX updated_at_index ON player');
        $this->addSql('DROP INDEX rank_index ON result');
    }
}
