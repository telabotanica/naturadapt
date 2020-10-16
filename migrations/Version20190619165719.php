<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190619165719 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE naturadapt_articles (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT NOT NULL, author_id INT NOT NULL, cover_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, edited_at DATETIME DEFAULT NULL, INDEX IDX_2BC23EBDD2112630 (usergroup_id), INDEX IDX_2BC23EBDF675F31B (author_id), UNIQUE INDEX UNIQ_2BC23EBD922726E9 (cover_id), INDEX slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE naturadapt_articles ADD CONSTRAINT FK_2BC23EBDD2112630 FOREIGN KEY (usergroup_id) REFERENCES naturadapt_usergroups (id)');
        $this->addSql('ALTER TABLE naturadapt_articles ADD CONSTRAINT FK_2BC23EBDF675F31B FOREIGN KEY (author_id) REFERENCES naturadapt_users (id)');
        $this->addSql('ALTER TABLE naturadapt_articles ADD CONSTRAINT FK_2BC23EBD922726E9 FOREIGN KEY (cover_id) REFERENCES naturadapt_files (id)');
        $this->addSql('ALTER TABLE naturadapt_document CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_usergroups_memberships CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE notifications_settings notifications_settings JSON DEFAULT NULL, CHANGE status status VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_files CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE size size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_users CHANGE avatar_id avatar_id INT DEFAULT NULL, CHANGE site_id site_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE display_name display_name VARCHAR(100) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(100) DEFAULT NULL, CHANGE country country VARCHAR(2) DEFAULT NULL, CHANGE profile_visibility profile_visibility VARCHAR(100) DEFAULT NULL, CHANGE inscription_type inscription_type VARCHAR(20) DEFAULT NULL, CHANGE locale locale VARCHAR(100) DEFAULT NULL, CHANGE timezone timezone VARCHAR(100) DEFAULT NULL, CHANGE seen_at seen_at DATETIME DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_pages CHANGE edited_at edited_at DATETIME DEFAULT NULL, CHANGE edition_restricted edition_restricted TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_usergroups CHANGE logo_id logo_id INT DEFAULT NULL, CHANGE cover_id cover_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE naturadapt_articles');
        $this->addSql('ALTER TABLE naturadapt_document CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE file_id file_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT \'\'NULL\'\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE naturadapt_files CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT \'\'NULL\'\' COLLATE utf8mb4_unicode_ci, CHANGE size size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_pages CHANGE edited_at edited_at DATETIME DEFAULT \'NULL\', CHANGE edition_restricted edition_restricted TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE naturadapt_usergroups CHANGE logo_id logo_id INT DEFAULT NULL, CHANGE cover_id cover_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_usergroups_memberships CHANGE role role VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE notifications_settings notifications_settings LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin, CHANGE status status VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE naturadapt_users CHANGE avatar_id avatar_id INT DEFAULT NULL, CHANGE site_id site_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE display_name display_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE zipcode zipcode VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE country country VARCHAR(2) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE profile_visibility profile_visibility VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE inscription_type inscription_type VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE locale locale VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE timezone timezone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE seen_at seen_at DATETIME DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE latitude latitude DOUBLE PRECISION DEFAULT \'NULL\', CHANGE longitude longitude DOUBLE PRECISION DEFAULT \'NULL\'');
    }
}
