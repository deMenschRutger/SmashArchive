<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Version20170317214822 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player ADD nationality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_98197A651C9DA55 ON player (nationality_id)');
        $this->addSql('ALTER TABLE tournament ADD country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_BD5FB8D9F92F3E70 ON tournament (country_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651C9DA55');
        $this->addSql('DROP INDEX IDX_98197A651C9DA55 ON player');
        $this->addSql('ALTER TABLE player DROP nationality_id');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9F92F3E70');
        $this->addSql('DROP INDEX IDX_BD5FB8D9F92F3E70 ON tournament');
        $this->addSql('ALTER TABLE tournament DROP country_id');
    }
}
