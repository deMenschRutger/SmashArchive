<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20180114152338 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournaments_organizers (tournament_id INT NOT NULL, player_profile_id INT NOT NULL, INDEX IDX_273F85B733D1A3E7 (tournament_id), INDEX IDX_273F85B7935F9685 (player_profile_id), PRIMARY KEY(tournament_id, player_profile_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournaments_organizers ADD CONSTRAINT FK_273F85B7935F9685 FOREIGN KEY (player_profile_id) REFERENCES player_profile (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tournaments_organizers');
    }
}
