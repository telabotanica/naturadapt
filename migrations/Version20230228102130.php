<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230228102130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_a317b8315e237e06 ON pollinisateurs_sites');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5FF00F115E237E06 ON pollinisateurs_sites (name)');
        $this->addSql('DROP INDEX uniq_504b31ba989d9b62 ON pollinisateurs_skills');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6BD9F6C5989D9B62 ON pollinisateurs_skills (slug)');
        $this->addSql('ALTER TABLE pollinisateurs_users DROP FOREIGN KEY FK_B94B7BB86383B10');
        $this->addSql('ALTER TABLE pollinisateurs_users DROP FOREIGN KEY FK_B94B7BBF6BD1646');
        $this->addSql('ALTER TABLE pollinisateurs_users ADD favorite_environment VARCHAR(20) DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX uniq_b94b7bbe7927c74 ON pollinisateurs_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F773009BE7927C74 ON pollinisateurs_users (email)');
        $this->addSql('DROP INDEX uniq_b94b7bb86383b10 ON pollinisateurs_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F773009B86383B10 ON pollinisateurs_users (avatar_id)');
        $this->addSql('DROP INDEX idx_b94b7bbf6bd1646 ON pollinisateurs_users');
        $this->addSql('CREATE INDEX IDX_F773009BF6BD1646 ON pollinisateurs_users (site_id)');
        $this->addSql('ALTER TABLE pollinisateurs_users ADD CONSTRAINT FK_B94B7BB86383B10 FOREIGN KEY (avatar_id) REFERENCES pollinisateurs_files (id)');
        $this->addSql('ALTER TABLE pollinisateurs_users ADD CONSTRAINT FK_B94B7BBF6BD1646 FOREIGN KEY (site_id) REFERENCES pollinisateurs_sites (id)');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills DROP FOREIGN KEY FK_FA38CFB55585C142');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills DROP FOREIGN KEY FK_FA38CFB5A76ED395');
        $this->addSql('DROP INDEX idx_fa38cfb5a76ed395 ON pollinisateurs_users_skills');
        $this->addSql('CREATE INDEX IDX_640E6734A76ED395 ON pollinisateurs_users_skills (user_id)');
        $this->addSql('DROP INDEX idx_fa38cfb55585c142 ON pollinisateurs_users_skills');
        $this->addSql('CREATE INDEX IDX_640E67345585C142 ON pollinisateurs_users_skills (skill_id)');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills ADD CONSTRAINT FK_FA38CFB55585C142 FOREIGN KEY (skill_id) REFERENCES pollinisateurs_skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills ADD CONSTRAINT FK_FA38CFB5A76ED395 FOREIGN KEY (user_id) REFERENCES pollinisateurs_users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pollinisateurs_users_skills DROP FOREIGN KEY FK_640E6734A76ED395');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills DROP FOREIGN KEY FK_640E67345585C142');
        $this->addSql('DROP INDEX idx_640e67345585c142 ON pollinisateurs_users_skills');
        $this->addSql('CREATE INDEX IDX_FA38CFB55585C142 ON pollinisateurs_users_skills (skill_id)');
        $this->addSql('DROP INDEX idx_640e6734a76ed395 ON pollinisateurs_users_skills');
        $this->addSql('CREATE INDEX IDX_FA38CFB5A76ED395 ON pollinisateurs_users_skills (user_id)');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills ADD CONSTRAINT FK_640E6734A76ED395 FOREIGN KEY (user_id) REFERENCES pollinisateurs_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pollinisateurs_users_skills ADD CONSTRAINT FK_640E67345585C142 FOREIGN KEY (skill_id) REFERENCES pollinisateurs_skills (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX uniq_6bd9f6c5989d9b62 ON pollinisateurs_skills');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_504B31BA989D9B62 ON pollinisateurs_skills (slug)');
        $this->addSql('DROP INDEX uniq_5ff00f115e237e06 ON pollinisateurs_sites');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A317B8315E237E06 ON pollinisateurs_sites (name)');
        $this->addSql('ALTER TABLE pollinisateurs_users DROP FOREIGN KEY FK_F773009B86383B10');
        $this->addSql('ALTER TABLE pollinisateurs_users DROP FOREIGN KEY FK_F773009BF6BD1646');
        $this->addSql('ALTER TABLE pollinisateurs_users DROP favorite_environment, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('DROP INDEX uniq_f773009be7927c74 ON pollinisateurs_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B94B7BBE7927C74 ON pollinisateurs_users (email)');
        $this->addSql('DROP INDEX uniq_f773009b86383b10 ON pollinisateurs_users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B94B7BB86383B10 ON pollinisateurs_users (avatar_id)');
        $this->addSql('DROP INDEX idx_f773009bf6bd1646 ON pollinisateurs_users');
        $this->addSql('CREATE INDEX IDX_B94B7BBF6BD1646 ON pollinisateurs_users (site_id)');
        $this->addSql('ALTER TABLE pollinisateurs_users ADD CONSTRAINT FK_F773009B86383B10 FOREIGN KEY (avatar_id) REFERENCES pollinisateurs_files (id)');
        $this->addSql('ALTER TABLE pollinisateurs_users ADD CONSTRAINT FK_F773009BF6BD1646 FOREIGN KEY (site_id) REFERENCES pollinisateurs_sites (id)');
    }
}
