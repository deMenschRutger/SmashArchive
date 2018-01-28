<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger.mensch@mediamonks.com>
 */
class Version20180120140505 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE players_mains (player_profile_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_4C4B6B2D935F9685 (player_profile_id), INDEX IDX_4C4B6B2D1136BE75 (character_id), PRIMARY KEY(player_profile_id, character_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE players_secondaries (player_profile_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_1BE0565B935F9685 (player_profile_id), INDEX IDX_1BE0565B1136BE75 (character_id), PRIMARY KEY(player_profile_id, character_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D935F9685 FOREIGN KEY (player_profile_id) REFERENCES player_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B935F9685 FOREIGN KEY (player_profile_id) REFERENCES player_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE players_mains');
        $this->addSql('DROP TABLE players_secondaries');
    }
}
