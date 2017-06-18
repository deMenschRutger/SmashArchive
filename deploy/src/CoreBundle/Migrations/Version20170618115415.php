<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170618115415 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE players_mains (player_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_4C4B6B2D99E6F5DF (player_id), INDEX IDX_4C4B6B2D1136BE75 (character_id), PRIMARY KEY(player_id, character_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players_secondaries (player_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_1BE0565B99E6F5DF (player_id), INDEX IDX_1BE0565B1136BE75 (character_id), PRIMARY KEY(player_id, character_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX name_game_unique ON characters (name, game_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE players_mains');
        $this->addSql('DROP TABLE players_secondaries');
        $this->addSql('DROP INDEX name_game_unique ON characters');
    }
}
