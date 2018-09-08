<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class Version20180901105948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rank (event_id INT NOT NULL, entrant_id INT NOT NULL, rank INT NOT NULL, INDEX IDX_8879E8E571F7E88B (event_id), INDEX IDX_8879E8E58BFF9D26 (entrant_id), INDEX rank_index (rank), PRIMARY KEY(event_id, entrant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rank ADD CONSTRAINT FK_8879E8E571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rank ADD CONSTRAINT FK_8879E8E58BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE ranking');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ranking (event_id INT NOT NULL, entrant_id INT NOT NULL, rank INT NOT NULL, INDEX IDX_80B839D071F7E88B (event_id), INDEX IDX_80B839D08BFF9D26 (entrant_id), INDEX rank_index (rank), PRIMARY KEY(event_id, entrant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ranking ADD CONSTRAINT FK_80B839D08BFF9D26 FOREIGN KEY (entrant_id) REFERENCES entrant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ranking ADD CONSTRAINT FK_80B839D071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE rank');
    }
}
