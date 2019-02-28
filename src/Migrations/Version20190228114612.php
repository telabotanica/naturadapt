<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190228114612 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, visibility VARCHAR(10) NOT NULL, active_apps LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups_categories (group_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_613B3FA8FE54D947 (group_id), INDEX IDX_613B3FA812469DE2 (category_id), PRIMARY KEY(group_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, location VARCHAR(100) DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, avatar VARCHAR(100) DEFAULT NULL, profile_visibility VARCHAR(100) DEFAULT NULL, locale VARCHAR(100) DEFAULT NULL, timezone VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, seen_at DATETIME DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups_memberships (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, grp_id INT NOT NULL, joined_at DATETIME NOT NULL, role VARCHAR(255) DEFAULT NULL, notifications_settings JSON DEFAULT NULL, INDEX IDX_1D654DBFA76ED395 (user_id), INDEX IDX_1D654DBFD51E9150 (grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groups_categories ADD CONSTRAINT FK_613B3FA8FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups_categories ADD CONSTRAINT FK_613B3FA812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups_memberships ADD CONSTRAINT FK_1D654DBFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE groups_memberships ADD CONSTRAINT FK_1D654DBFD51E9150 FOREIGN KEY (grp_id) REFERENCES groups (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE groups_categories DROP FOREIGN KEY FK_613B3FA812469DE2');
        $this->addSql('ALTER TABLE groups_categories DROP FOREIGN KEY FK_613B3FA8FE54D947');
        $this->addSql('ALTER TABLE groups_memberships DROP FOREIGN KEY FK_1D654DBFD51E9150');
        $this->addSql('ALTER TABLE groups_memberships DROP FOREIGN KEY FK_1D654DBFA76ED395');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE groups_categories');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE groups_memberships');
    }
}
