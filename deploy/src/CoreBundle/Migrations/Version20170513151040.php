<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170513151040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC11371F7E88B');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC1138BFF9D26');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC11371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1138BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651C9DA55');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65F92F3E70');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA733D1A3E7');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE phase DROP FOREIGN KEY FK_B1BDD6CB71F7E88B');
        $this->addSql('ALTER TABLE phase ADD CONSTRAINT FK_B1BDD6CB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group DROP FOREIGN KEY FK_B1E6CEB799091188');
        $this->addSql('ALTER TABLE phase_group ADD CONSTRAINT FK_B1E6CEB799091188 FOREIGN KEY (phase_id) REFERENCES phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9F92F3E70');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F1BCAA5F6');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F5DFCD4B8');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FB1894328');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FBA409099');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FDAD5A4E7');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F1BCAA5F6 FOREIGN KEY (loser_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FB1894328 FOREIGN KEY (entrant_one_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FBA409099 FOREIGN KEY (phase_group_id) REFERENCES phase_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FDAD5A4E7 FOREIGN KEY (entrant_two_id) REFERENCES entrant (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA733D1A3E7');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE phase DROP FOREIGN KEY FK_B1BDD6CB71F7E88B');
        $this->addSql('ALTER TABLE phase ADD CONSTRAINT FK_B1BDD6CB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE phase_group DROP FOREIGN KEY FK_B1E6CEB799091188');
        $this->addSql('ALTER TABLE phase_group ADD CONSTRAINT FK_B1E6CEB799091188 FOREIGN KEY (phase_id) REFERENCES phase (id)');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FBA409099');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FB1894328');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263FDAD5A4E7');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F5DFCD4B8');
        $this->addSql('ALTER TABLE phase_group_set DROP FOREIGN KEY FK_4A9263F1BCAA5F6');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FBA409099 FOREIGN KEY (phase_group_id) REFERENCES phase_group (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FB1894328 FOREIGN KEY (entrant_one_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263FDAD5A4E7 FOREIGN KEY (entrant_two_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phase_group_set ADD CONSTRAINT FK_4A9263F1BCAA5F6 FOREIGN KEY (loser_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651C9DA55');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65F92F3E70');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC11371F7E88B');
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC1138BFF9D26');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC11371F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1138BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9F92F3E70');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
    }
}
