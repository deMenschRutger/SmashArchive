<?php

declare(strict_types=1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170312131914 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player CHANGE original_id original_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE phase_group CHANGE original_id original_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament CHANGE original_id original_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE phase_group_set CHANGE original_id original_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phase_group CHANGE original_id original_id INT NOT NULL');
        $this->addSql('ALTER TABLE phase_group_set CHANGE original_id original_id INT NOT NULL');
        $this->addSql('ALTER TABLE player CHANGE original_id original_id INT NOT NULL');
        $this->addSql('ALTER TABLE tournament CHANGE original_id original_id INT NOT NULL');
    }
}
