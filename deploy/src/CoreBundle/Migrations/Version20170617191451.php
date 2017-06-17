<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170617191451 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE characters ADD game_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410EE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3A29410EE48FD905 ON characters (game_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE characters DROP FOREIGN KEY FK_3A29410EE48FD905');
        $this->addSql('DROP INDEX IDX_3A29410EE48FD905 ON characters');
        $this->addSql('ALTER TABLE characters DROP game_id');
    }
}
