<?php

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *
 */
class Version20170121233143 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE entrant (id INT AUTO_INCREMENT NOT NULL, smashgg_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phaseGroupSet (id INT AUTO_INCREMENT NOT NULL, phase_group_id INT DEFAULT NULL, entrant_one_id INT DEFAULT NULL, entrant_two_id INT DEFAULT NULL, smashgg_id INT DEFAULT NULL, INDEX IDX_E61425DCBA409099 (phase_group_id), INDEX IDX_E61425DCB1894328 (entrant_one_id), INDEX IDX_E61425DCDAD5A4E7 (entrant_two_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE phaseGroupSet ADD CONSTRAINT FK_E61425DCBA409099 FOREIGN KEY (phase_group_id) REFERENCES phase_group (id)');
        $this->addSql('ALTER TABLE phaseGroupSet ADD CONSTRAINT FK_E61425DCB1894328 FOREIGN KEY (entrant_one_id) REFERENCES entrant (id)');
        $this->addSql('ALTER TABLE phaseGroupSet ADD CONSTRAINT FK_E61425DCDAD5A4E7 FOREIGN KEY (entrant_two_id) REFERENCES entrant (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phaseGroupSet DROP FOREIGN KEY FK_E61425DCB1894328');
        $this->addSql('ALTER TABLE phaseGroupSet DROP FOREIGN KEY FK_E61425DCDAD5A4E7');
        $this->addSql('DROP TABLE entrant');
        $this->addSql('DROP TABLE phaseGroupSet');
    }
}
