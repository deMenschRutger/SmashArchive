<?php

declare(strict_types=1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170219140625 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player ADD original_id INT NOT NULL');
        $this->addSql('ALTER TABLE phase_group ADD original_id INT NOT NULL');
        $this->addSql('ALTER TABLE tournament ADD original_id INT NOT NULL');
        $this->addSql('ALTER TABLE phase_group_set ADD original_id INT NOT NULL, ADD winner_score INT DEFAULT NULL, ADD loser_score INT DEFAULT NULL, ADD is_forfeit TINYINT(1) NOT NULL, ADD is_ranked TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phase_group DROP original_id');
        $this->addSql('ALTER TABLE phase_group_set DROP original_id, DROP winner_score, DROP loser_score, DROP is_forfeit, DROP is_ranked');
        $this->addSql('ALTER TABLE player DROP original_id');
        $this->addSql('ALTER TABLE tournament DROP original_id');
    }
}
