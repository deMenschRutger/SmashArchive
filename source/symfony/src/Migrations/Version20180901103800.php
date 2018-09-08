<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class Version20180901103800 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE characters (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, INDEX IDX_3A29410EE48FD905 (game_id), UNIQUE INDEX name_game_unique (name, game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE series (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(128) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3A10012D989D9B62 (slug), INDEX name_index (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, origin_tournament_id INT DEFAULT NULL, profile_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_98197A651AC6034F (origin_tournament_id), INDEX IDX_98197A65CCFA12B8 (profile_id), INDEX name_index (name), INDEX type_index (type), INDEX external_id_index (external_id), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, game_id INT DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, entrant_count INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3BAE0AA733D1A3E7 (tournament_id), INDEX IDX_3BAE0AA7E48FD905 (game_id), INDEX external_id_index (external_id), INDEX name_index (name), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrant (id INT AUTO_INCREMENT NOT NULL, origin_phase_id INT DEFAULT NULL, parent_entrant_id INT DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, is_new TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5E7BAE15AABF7B78 (origin_phase_id), UNIQUE INDEX UNIQ_5E7BAE15CCBC1976 (parent_entrant_id), INDEX external_id_index (external_id), INDEX name_index (name), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entrants_players (entrant_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_997889DA8BFF9D26 (entrant_id), INDEX IDX_997889DA99E6F5DF (player_id), PRIMARY KEY(entrant_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(4) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5373C96677153098 (code), INDEX name_index (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, phase_order INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B1BDD6CB71F7E88B (event_id), INDEX external_id_index (external_id), INDEX name_index (name), INDEX phase_order_index (phase_order), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, display_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), INDEX smashgg_index (smashgg_id), INDEX name_index (name), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase_group (id INT AUTO_INCREMENT NOT NULL, phase_id INT DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, results_page LONGTEXT DEFAULT NULL, smash_ranking_info LONGTEXT DEFAULT NULL, type SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B1E6CEB799091188 (phase_id), INDEX external_id_index (external_id), INDEX name_index (name), INDEX type_index (type), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, series_id INT DEFAULT NULL, source VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, date_start DATE DEFAULT NULL, smashgg_url LONGTEXT DEFAULT NULL, facebook_event_url LONGTEXT DEFAULT NULL, results_page LONGTEXT DEFAULT NULL, player_count INT DEFAULT NULL, is_complete TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BD5FB8D9989D9B62 (slug), INDEX IDX_BD5FB8D9F92F3E70 (country_id), INDEX IDX_BD5FB8D95278319C (series_id), INDEX external_id_index (external_id), INDEX name_index (name), INDEX region_index (region), INDEX city_index (city), INDEX date_start_index (date_start), INDEX is_active_index (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournaments_organizers (tournament_id INT NOT NULL, profile_id INT NOT NULL, INDEX IDX_273F85B733D1A3E7 (tournament_id), INDEX IDX_273F85B7CCFA12B8 (profile_id), PRIMARY KEY(tournament_id, profile_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ranking (event_id INT NOT NULL, entrant_id INT NOT NULL, rank INT NOT NULL, INDEX IDX_80B839D071F7E88B (event_id), INDEX IDX_80B839D08BFF9D26 (entrant_id), INDEX rank_index (rank), PRIMARY KEY(event_id, entrant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, nationality_id INT DEFAULT NULL, country_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, name VARCHAR(255) DEFAULT NULL, gamer_tag VARCHAR(255) NOT NULL, region VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, is_competing TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, properties LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8157AA0F989D9B62 (slug), INDEX IDX_8157AA0F1C9DA55 (nationality_id), INDEX IDX_8157AA0FF92F3E70 (country_id), INDEX slug_index (slug), INDEX gamer_tag_index (gamer_tag), INDEX name_index (name), INDEX region_index (region), INDEX city_index (city), INDEX is_competing_index (is_competing), INDEX is_active_index (is_active), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players_mains (player_profile_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_4C4B6B2D935F9685 (player_profile_id), INDEX IDX_4C4B6B2D1136BE75 (character_id), PRIMARY KEY(player_profile_id, character_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players_secondaries (player_profile_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_1BE0565B935F9685 (player_profile_id), INDEX IDX_1BE0565B1136BE75 (character_id), PRIMARY KEY(player_profile_id, character_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phase_group_set (id INT AUTO_INCREMENT NOT NULL, phase_group_id INT DEFAULT NULL, entrant_one_id INT DEFAULT NULL, entrant_two_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, loser_id INT DEFAULT NULL, external_id VARCHAR(255) DEFAULT NULL, round INT NOT NULL, round_name VARCHAR(255) DEFAULT NULL, is_finals TINYINT(1) NOT NULL, is_grand_finals TINYINT(1) NOT NULL, winner_score INT DEFAULT NULL, loser_score INT DEFAULT NULL, is_ranked TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, is_orphaned TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4A9263FBA409099 (phase_group_id), INDEX IDX_4A9263FB1894328 (entrant_one_id), INDEX IDX_4A9263FDAD5A4E7 (entrant_two_id), INDEX IDX_4A9263F5DFCD4B8 (winner_id), INDEX IDX_4A9263F1BCAA5F6 (loser_id), INDEX external_id_index (external_id), INDEX round_index (round), INDEX is_ranked_index (is_ranked), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410EE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651AC6034F FOREIGN KEY (origin_tournament_id) REFERENCES tournament (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15AABF7B78 FOREIGN KEY (origin_phase_id) REFERENCES phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15CCBC1976 FOREIGN KEY (parent_entrant_id) REFERENCES entrant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE entrants_players ADD CONSTRAINT FK_997889DA8BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entrants_players ADD CONSTRAINT FK_997889DA99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase ADD CONSTRAINT FK_B1BDD6CB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group ADD CONSTRAINT FK_B1E6CEB799091188 FOREIGN KEY (phase_id) REFERENCES phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D95278319C FOREIGN KEY (series_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B7CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ranking ADD CONSTRAINT FK_80B839D071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ranking ADD CONSTRAINT FK_80B839D08BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F1C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D935F9685 FOREIGN KEY (player_profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B935F9685 FOREIGN KEY (player_profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FBA409099 FOREIGN KEY (phase_group_id) REFERENCES phase_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FB1894328 FOREIGN KEY (entrant_one_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FDAD5A4E7 FOREIGN KEY (entrant_two_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F1BCAA5F6 FOREIGN KEY (loser_id) REFERENCES entrant (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE players_mains DROP FOREIGN KEY FK_4C4B6B2D1136BE75');
        $this->addSql('ALTER TABLE players_secondaries DROP FOREIGN KEY FK_1BE0565B1136BE75');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D95278319C');
        $this->addSql('ALTER TABLE entrants_players DROP FOREIGN KEY FK_997889DA99E6F5DF');
        $this->addSql('ALTER TABLE phase DROP FOREIGN KEY FK_B1BDD6CB71F7E88B');
        $this->addSql('ALTER TABLE ranking DROP FOREIGN KEY FK_80B839D071F7E88B');
        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15CCBC1976');
        $this->addSql('ALTER TABLE entrants_players DROP FOREIGN KEY FK_997889DA8BFF9D26');
        $this->addSql('ALTER TABLE ranking DROP FOREIGN KEY FK_80B839D08BFF9D26');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FB1894328');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FDAD5A4E7');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F5DFCD4B8');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F1BCAA5F6');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9F92F3E70');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F1C9DA55');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FF92F3E70');
        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15AABF7B78');
        $this->addSql('ALTER TABLE phase_group DROP FOREIGN KEY FK_B1E6CEB799091188');
        $this->addSql('ALTER TABLE characters DROP FOREIGN KEY FK_3A29410EE48FD905');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FBA409099');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651AC6034F');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA733D1A3E7');
        $this->addSql('ALTER TABLE tournaments_organizers DROP FOREIGN KEY FK_273F85B733D1A3E7');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65CCFA12B8');
        $this->addSql('ALTER TABLE tournaments_organizers DROP FOREIGN KEY FK_273F85B7CCFA12B8');
        $this->addSql('ALTER TABLE players_mains DROP FOREIGN KEY FK_4C4B6B2D935F9685');
        $this->addSql('ALTER TABLE players_secondaries DROP FOREIGN KEY FK_1BE0565B935F9685');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE series');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE entrant');
        $this->addSql('DROP TABLE entrants_players');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE phase');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE phase_group');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournaments_organizers');
        $this->addSql('DROP TABLE ranking');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE players_mains');
        $this->addSql('DROP TABLE players_secondaries');
        $this->addSql('DROP TABLE phase_group_set');
    }
}
