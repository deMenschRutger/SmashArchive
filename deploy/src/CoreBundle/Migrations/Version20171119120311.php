<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20171119120311 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player CHANGE original_id smash_ranking_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX smashgg_slug_index ON tournament');
        $this->addSql('UPDATE tournament SET smashgg_slug = original_id WHERE smashgg_slug IS NULL AND source = "custom"');
        $this->addSql('UPDATE tournament SET source = "smashranking" WHERE source = "custom"');
        $this->addSql('ALTER TABLE tournament DROP original_id, CHANGE smashgg_slug external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX external_id_index ON tournament (external_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player CHANGE smash_ranking_id original_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX external_id_index ON tournament');
        $this->addSql('ALTER TABLE tournament ADD original_id INT DEFAULT NULL, CHANGE external_id smashgg_slug VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE INDEX smashgg_slug_index ON tournament (smashgg_slug)');
    }
}
