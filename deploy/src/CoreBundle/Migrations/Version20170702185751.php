<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170702185751 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournaments_organizers (tournament_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_273F85B733D1A3E7 (tournament_id), INDEX IDX_273F85B799E6F5DF (player_id), PRIMARY KEY(tournament_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament ADD smashgg_url LONGTEXT DEFAULT NULL, ADD facebook_event_url LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tournaments_organizers');
        $this->addSql('ALTER TABLE tournament DROP smashgg_url, DROP facebook_event_url');
    }
}
