<?php

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *
 */
class Version20170122225126 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, smashgg_id INT DEFAULT NULL, gamer_tag VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, game_id INT DEFAULT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_3BAE0AA733D1A3E7 (tournament_id), INDEX IDX_3BAE0AA7E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrant (id INT AUTO_INCREMENT NOT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrants_players (entrant_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_997889DA8BFF9D26 (entrant_id), INDEX IDX_997889DA99E6F5DF (player_id), PRIMARY KEY(entrant_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, phaseOrder INT NOT NULL, INDEX IDX_B1BDD6CB71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase_group (id INT AUTO_INCREMENT NOT NULL, phase_id INT DEFAULT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type SMALLINT NOT NULL, INDEX IDX_B1E6CEB799091188 (phase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, smashgg_slug VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BD5FB8D9989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase_group_set (id INT AUTO_INCREMENT NOT NULL, winner_id INT DEFAULT NULL, loser_id INT DEFAULT NULL, phase_group_id INT DEFAULT NULL, entrant_one_id INT DEFAULT NULL, entrant_two_id INT DEFAULT NULL, smashgg_id INT DEFAULT NULL, round INT NOT NULL, INDEX IDX_4A9263F5DFCD4B8 (winner_id), INDEX IDX_4A9263F1BCAA5F6 (loser_id), INDEX IDX_4A9263FBA409099 (phase_group_id), INDEX IDX_4A9263FB1894328 (entrant_one_id), INDEX IDX_4A9263FDAD5A4E7 (entrant_two_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE entrants_players ADD CONSTRAINT FK_997889DA8BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entrants_players ADD CONSTRAINT FK_997889DA99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase ADD CONSTRAINT FK_B1BDD6CB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE phase_group ADD CONSTRAINT FK_B1E6CEB799091188 FOREIGN KEY (phase_id) REFERENCES phase (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F1BCAA5F6 FOREIGN KEY (loser_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FBA409099 FOREIGN KEY (phase_group_id) REFERENCES phase_group (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FB1894328 FOREIGN KEY (entrant_one_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FDAD5A4E7 FOREIGN KEY (entrant_two_id) REFERENCES entrant (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrants_players DROP FOREIGN KEY FK_997889DA99E6F5DF');
        $this->addSql('ALTER TABLE phase DROP FOREIGN KEY FK_B1BDD6CB71F7E88B');
        $this->addSql('ALTER TABLE entrants_players DROP FOREIGN KEY FK_997889DA8BFF9D26');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F5DFCD4B8');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F1BCAA5F6');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FB1894328');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FDAD5A4E7');
        $this->addSql('ALTER TABLE phase_group DROP FOREIGN KEY FK_B1E6CEB799091188');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FBA409099');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA733D1A3E7');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE entrant');
        $this->addSql('DROP TABLE entrants_players');
        $this->addSql('DROP TABLE phase');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE phase_group');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE phase_group_set');
    }
}
