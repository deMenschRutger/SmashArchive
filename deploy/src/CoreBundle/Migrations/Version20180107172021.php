<?php

declare(strict_types = 1);

namespace CoreBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @author Rutger Mensch <rutger.mensch@mediamonks.com>
 */
class Version20180107172021 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player_profile (id INT AUTO_INCREMENT NOT NULL, nationality_id INT DEFAULT NULL, country_id INT DEFAULT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, gamer_tag VARCHAR(255) NOT NULL, region VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, is_competing TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, properties LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E0A3554A989D9B62 (slug), INDEX IDX_E0A3554A1C9DA55 (nationality_id), INDEX IDX_E0A3554AF92F3E70 (country_id), INDEX slug_index (slug), INDEX gamer_tag_index (gamer_tag), INDEX name_index (name), INDEX region_index (region), INDEX city_index (city), INDEX is_competing_index (is_competing), INDEX is_active_index (is_active), INDEX created_at_index (created_at), INDEX updated_at_index (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('INSERT INTO player_profile (id, nationality_id, country_id, slug, name, gamer_tag, region, city, is_competing, is_active, properties, created_at, updated_at) SELECT id, nationality_id, country_id, slug, name, gamer_tag, region, city, is_competing, is_active, properties, created_at, updated_at FROM player WHERE smash_ranking_id IS NOT NULL');

        $this->addSql('ALTER TABLE player_profile ADD CONSTRAINT FK_E0A3554A1C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE player_profile ADD CONSTRAINT FK_E0A3554AF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE players_mains DROP FOREIGN KEY FK_4C4B6B2D99E6F5DF');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D99E6F5DF FOREIGN KEY (player_id) REFERENCES player_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries DROP FOREIGN KEY FK_1BE0565B99E6F5DF');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B99E6F5DF FOREIGN KEY (player_id) REFERENCES player_profile (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A651C9DA55');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65AD5287F3');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65F92F3E70');
        $this->addSql('DROP INDEX UNIQ_98197A65989D9B62 ON player');
        $this->addSql('DROP INDEX UNIQ_98197A65AD5287F3 ON player');
        $this->addSql('DROP INDEX IDX_98197A65F92F3E70 ON player');
        $this->addSql('DROP INDEX IDX_98197A651C9DA55 ON player');
        $this->addSql('DROP INDEX smashgg_index ON player');
        $this->addSql('DROP INDEX slug_index ON player');
        $this->addSql('DROP INDEX gamer_tag_index ON player');
        $this->addSql('DROP INDEX region_index ON player');
        $this->addSql('DROP INDEX city_index ON player');
        $this->addSql('DROP INDEX is_competing_index ON player');
        $this->addSql('DROP INDEX is_active_index ON player');
        $this->addSql('DROP INDEX is_new_index ON player');
        $this->addSql('ALTER TABLE player ADD player_profile_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD external_id VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE player SET name = gamer_tag');
        $this->addSql('UPDATE player SET player_profile_id = id WHERE smash_ranking_id IS NOT NULL');
        $this->addSql("UPDATE player SET type = 'smashranking', external_id = smash_ranking_id WHERE smash_ranking_id IS NOT NULL");
        $this->addSql("UPDATE player SET type = 'smashgg', external_id = smashgg_id WHERE smash_ranking_id IS NULL AND smashgg_id IS NOT NULL");

        $this->addSql('ALTER TABLE player DROP nationality_id, DROP target_player_id, DROP country_id, DROP smashgg_id, DROP gamer_tag, DROP slug, DROP region, DROP city, DROP is_competing, DROP is_active, DROP smash_ranking_id, DROP properties, DROP is_new, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65935F9685 FOREIGN KEY (player_profile_id) REFERENCES player_profile (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_98197A65935F9685 ON player (player_profile_id)');
        $this->addSql('CREATE INDEX type_index ON player (type)');
        $this->addSql('CREATE INDEX external_id_index ON player (external_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE players_mains DROP FOREIGN KEY FK_4C4B6B2D99E6F5DF');
        $this->addSql('ALTER TABLE players_secondaries DROP FOREIGN KEY FK_1BE0565B99E6F5DF');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65935F9685');
        $this->addSql('DROP TABLE player_profile');
        $this->addSql('DROP INDEX IDX_98197A65935F9685 ON player');
        $this->addSql('DROP INDEX type_index ON player');
        $this->addSql('DROP INDEX external_id_index ON player');
        $this->addSql('ALTER TABLE player ADD target_player_id INT DEFAULT NULL, ADD country_id INT DEFAULT NULL, ADD smashgg_id INT DEFAULT NULL, ADD slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD city VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD is_competing TINYINT(1) NOT NULL, ADD is_active TINYINT(1) NOT NULL, ADD smash_ranking_id INT DEFAULT NULL, ADD properties LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', ADD is_new TINYINT(1) NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE player_profile_id nationality_id INT DEFAULT NULL, CHANGE type gamer_tag VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE external_id region VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651C9DA55 FOREIGN KEY (nationality_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65AD5287F3 FOREIGN KEY (target_player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65989D9B62 ON player (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65AD5287F3 ON player (target_player_id)');
        $this->addSql('CREATE INDEX IDX_98197A65F92F3E70 ON player (country_id)');
        $this->addSql('CREATE INDEX IDX_98197A651C9DA55 ON player (nationality_id)');
        $this->addSql('CREATE INDEX smashgg_index ON player (smashgg_id)');
        $this->addSql('CREATE INDEX slug_index ON player (slug)');
        $this->addSql('CREATE INDEX gamer_tag_index ON player (gamer_tag)');
        $this->addSql('CREATE INDEX region_index ON player (region)');
        $this->addSql('CREATE INDEX city_index ON player (city)');
        $this->addSql('CREATE INDEX is_competing_index ON player (is_competing)');
        $this->addSql('CREATE INDEX is_active_index ON player (is_active)');
        $this->addSql('CREATE INDEX is_new_index ON player (is_new)');
        $this->addSql('ALTER TABLE players_mains DROP FOREIGN KEY FK_4C4B6B2D99E6F5DF');
        $this->addSql('ALTER TABLE players_mains ADD CONSTRAINT FK_4C4B6B2D99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE players_secondaries DROP FOREIGN KEY FK_1BE0565B99E6F5DF');
        $this->addSql('ALTER TABLE players_secondaries ADD CONSTRAINT FK_1BE0565B99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }
}
