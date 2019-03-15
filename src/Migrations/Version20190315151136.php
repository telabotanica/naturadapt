<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190315151136 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usergroups_memberships (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, usergroup_id INT NOT NULL, joined_at DATETIME NOT NULL, role VARCHAR(255) DEFAULT NULL, notifications_settings JSON DEFAULT NULL, INDEX IDX_29DF4074A76ED395 (user_id), INDEX IDX_29DF4074D2112630 (usergroup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE files (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, usergroup_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_6354059A76ED395 (user_id), INDEX IDX_6354059D2112630 (usergroup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, location VARCHAR(100) DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, avatar VARCHAR(100) DEFAULT NULL, profile_visibility VARCHAR(100) DEFAULT NULL, locale VARCHAR(100) DEFAULT NULL, timezone VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pages (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT NOT NULL, author_id INT NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, edited_at DATETIME DEFAULT NULL, INDEX IDX_2074E575D2112630 (usergroup_id), INDEX IDX_2074E575F675F31B (author_id), INDEX slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usergroups (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, visibility VARCHAR(10) NOT NULL, active_apps LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_98972EB4989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usergroups_categories (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B5EF08C1D2112630 (usergroup_id), INDEX IDX_B5EF08C112469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usergroups_memberships ADD CONSTRAINT FK_29DF4074A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE usergroups_memberships ADD CONSTRAINT FK_29DF4074D2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id)');
        $this->addSql('ALTER TABLE files ADD CONSTRAINT FK_6354059A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE files ADD CONSTRAINT FK_6354059D2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575D2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE usergroups_categories ADD CONSTRAINT FK_B5EF08C1D2112630 FOREIGN KEY (usergroup_id) REFERENCES usergroups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usergroups_categories ADD CONSTRAINT FK_B5EF08C112469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE usergroups_categories DROP FOREIGN KEY FK_B5EF08C112469DE2');
        $this->addSql('ALTER TABLE usergroups_memberships DROP FOREIGN KEY FK_29DF4074A76ED395');
        $this->addSql('ALTER TABLE files DROP FOREIGN KEY FK_6354059A76ED395');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575F675F31B');
        $this->addSql('ALTER TABLE usergroups_memberships DROP FOREIGN KEY FK_29DF4074D2112630');
        $this->addSql('ALTER TABLE files DROP FOREIGN KEY FK_6354059D2112630');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575D2112630');
        $this->addSql('ALTER TABLE usergroups_categories DROP FOREIGN KEY FK_B5EF08C1D2112630');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE usergroups_memberships');
        $this->addSql('DROP TABLE files');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE usergroups');
        $this->addSql('DROP TABLE usergroups_categories');
    }
}
