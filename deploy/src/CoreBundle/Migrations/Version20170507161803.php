<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170507161803 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX smashgg_slug_index ON tournament (smashgg_slug)');
        $this->addSql('CREATE INDEX name_index ON tournament (name)');
        $this->addSql('CREATE INDEX region_index ON tournament (region)');
        $this->addSql('CREATE INDEX city_index ON tournament (city)');
        $this->addSql('CREATE INDEX date_start_index ON tournament (date_start)');
        $this->addSql('CREATE INDEX is_active_index ON tournament (is_active)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX smashgg_slug_index ON tournament');
        $this->addSql('DROP INDEX name_index ON tournament');
        $this->addSql('DROP INDEX region_index ON tournament');
        $this->addSql('DROP INDEX city_index ON tournament');
        $this->addSql('DROP INDEX date_start_index ON tournament');
        $this->addSql('DROP INDEX is_active_index ON tournament');
    }
}
