<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214165219 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE naturadapt_discussion_message (id INT AUTO_INCREMENT NOT NULL, discussion_id INT NOT NULL, author_id INT NOT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_E67EECB61ADED311 (discussion_id), INDEX IDX_E67EECB6F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE naturadapt_discussion_message ADD CONSTRAINT FK_E67EECB61ADED311 FOREIGN KEY (discussion_id) REFERENCES naturadapt_discussion (id)');
        $this->addSql('ALTER TABLE naturadapt_discussion_message ADD CONSTRAINT FK_E67EECB6F675F31B FOREIGN KEY (author_id) REFERENCES naturadapt_users (id)');
        $this->addSql('ALTER TABLE naturadapt_usergroups_memberships CHANGE notifications_settings notifications_settings JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE naturadapt_users CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE naturadapt_pages_revisions CHANGE data data JSON DEFAULT NULL');
        $this->addSql('DROP INDEX type ON naturadapt_log_events');
        $this->addSql('ALTER TABLE naturadapt_log_events CHANGE data data JSON DEFAULT NULL');
        $this->addSql('CREATE INDEX type ON naturadapt_log_events (type)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE naturadapt_discussion_message');
        $this->addSql('DROP INDEX type ON naturadapt_log_events');
        $this->addSql('ALTER TABLE naturadapt_log_events CHANGE data data TEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE INDEX type ON naturadapt_log_events (type(191))');
        $this->addSql('ALTER TABLE naturadapt_pages_revisions CHANGE data data TEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE naturadapt_usergroups_memberships CHANGE notifications_settings notifications_settings TEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE naturadapt_users CHANGE roles roles TEXT NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
