<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20180114135126 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651AC6034F');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651AC6034F FOREIGN KEY (origin_tournament_id) REFERENCES tournament (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15CCBC1976');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15CCBC1976 FOREIGN KEY (parent_entrant_id) REFERENCES entrant (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entrant DROP FOREIGN KEY FK_5E7BAE15CCBC1976');
        $this->addSql('ALTER TABLE entrant ADD CONSTRAINT FK_5E7BAE15CCBC1976 FOREIGN KEY (parent_entrant_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651AC6034F');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651AC6034F FOREIGN KEY (origin_tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
    }
}
