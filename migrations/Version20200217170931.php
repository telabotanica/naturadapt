<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200217170931 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE naturadapt_discussion_message (id INT AUTO_INCREMENT NOT NULL, discussion_id INT NOT NULL, author_id INT NOT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, masked TINYINT(1) DEFAULT NULL, INDEX IDX_E67EECB61ADED311 (discussion_id), INDEX IDX_E67EECB6F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE naturadapt_discussion (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT NOT NULL, author_id INT NOT NULL, uuid VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, active_at DATETIME DEFAULT NULL, INDEX IDX_AC4941DBD2112630 (usergroup_id), INDEX IDX_AC4941DBF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE naturadapt_discussion_message ADD CONSTRAINT FK_E67EECB61ADED311 FOREIGN KEY (discussion_id) REFERENCES naturadapt_discussion (id)');
        $this->addSql('ALTER TABLE naturadapt_discussion_message ADD CONSTRAINT FK_E67EECB6F675F31B FOREIGN KEY (author_id) REFERENCES naturadapt_users (id)');
        $this->addSql('ALTER TABLE naturadapt_discussion ADD CONSTRAINT FK_AC4941DBD2112630 FOREIGN KEY (usergroup_id) REFERENCES naturadapt_usergroups (id)');
        $this->addSql('ALTER TABLE naturadapt_discussion ADD CONSTRAINT FK_AC4941DBF675F31B FOREIGN KEY (author_id) REFERENCES naturadapt_users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE naturadapt_discussion_message DROP FOREIGN KEY FK_E67EECB61ADED311');
        $this->addSql('DROP TABLE naturadapt_discussion_message');
        $this->addSql('DROP TABLE naturadapt_discussion');
    }
}
