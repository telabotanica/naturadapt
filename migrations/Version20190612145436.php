<?php declare( strict_types=1 );

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190612145436 extends AbstractMigration {
	public function up ( Schema $schema ): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf( $this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.' );

		$this->addSql( 'CREATE TABLE pollinisateurs_document (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, usergroup_id INT DEFAULT NULL, file_id INT DEFAULT NULL, INDEX IDX_4C7685A3A76ED395 (user_id), INDEX IDX_4C7685A3D2112630 (usergroup_id), INDEX IDX_4C7685A393CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB' );
		$this->addSql( 'ALTER TABLE pollinisateurs_document ADD CONSTRAINT FK_4C7685A3A76ED395 FOREIGN KEY (user_id) REFERENCES pollinisateurs_users (id)' );
		$this->addSql( 'ALTER TABLE pollinisateurs_document ADD CONSTRAINT FK_4C7685A3D2112630 FOREIGN KEY (usergroup_id) REFERENCES pollinisateurs_usergroups (id)' );
		$this->addSql( 'ALTER TABLE pollinisateurs_document ADD CONSTRAINT FK_4C7685A393CB796C FOREIGN KEY (file_id) REFERENCES pollinisateurs_files (id)' );
		$this->addSql( 'ALTER TABLE pollinisateurs_usergroups_memberships CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE notifications_settings notifications_settings JSON DEFAULT NULL, CHANGE status status VARCHAR(32) DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_files CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE size size INT DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_users CHANGE avatar_id avatar_id INT DEFAULT NULL, CHANGE site_id site_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE display_name display_name VARCHAR(100) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(100) DEFAULT NULL, CHANGE country country VARCHAR(2) DEFAULT NULL, CHANGE profile_visibility profile_visibility VARCHAR(100) DEFAULT NULL, CHANGE inscription_type inscription_type VARCHAR(20) DEFAULT NULL, CHANGE locale locale VARCHAR(100) DEFAULT NULL, CHANGE timezone timezone VARCHAR(100) DEFAULT NULL, CHANGE seen_at seen_at DATETIME DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_pages CHANGE edited_at edited_at DATETIME DEFAULT NULL, CHANGE edition_restricted edition_restricted TINYINT(1) DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_usergroups CHANGE logo_id logo_id INT DEFAULT NULL, CHANGE cover_id cover_id INT DEFAULT NULL' );
	}

	public function down ( Schema $schema ): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf( $this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.' );

		$this->addSql( 'DROP TABLE pollinisateurs_document' );
		$this->addSql( 'ALTER TABLE pollinisateurs_files CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE size size INT DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_pages CHANGE edited_at edited_at DATETIME DEFAULT \'NULL\', CHANGE edition_restricted edition_restricted TINYINT(1) DEFAULT \'NULL\'' );
		$this->addSql( 'ALTER TABLE pollinisateurs_usergroups CHANGE logo_id logo_id INT DEFAULT NULL, CHANGE cover_id cover_id INT DEFAULT NULL' );
		$this->addSql( 'ALTER TABLE pollinisateurs_usergroups_memberships CHANGE role role VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE notifications_settings notifications_settings LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin, CHANGE status status VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci' );
		$this->addSql( 'ALTER TABLE pollinisateurs_users CHANGE avatar_id avatar_id INT DEFAULT NULL, CHANGE site_id site_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE display_name display_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE zipcode zipcode VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE country country VARCHAR(2) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE profile_visibility profile_visibility VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE inscription_type inscription_type VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE locale locale VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE timezone timezone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE seen_at seen_at DATETIME DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE latitude latitude DOUBLE PRECISION DEFAULT \'NULL\', CHANGE longitude longitude DOUBLE PRECISION DEFAULT \'NULL\'' );
	}
}
