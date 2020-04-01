<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327104116 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE naturadapt_discussion_message_file (discussion_message_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_84DF540FD1F18135 (discussion_message_id), INDEX IDX_84DF540F93CB796C (file_id), PRIMARY KEY(discussion_message_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE naturadapt_discussion_message_file ADD CONSTRAINT FK_84DF540FD1F18135 FOREIGN KEY (discussion_message_id) REFERENCES naturadapt_discussion_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE naturadapt_discussion_message_file ADD CONSTRAINT FK_84DF540F93CB796C FOREIGN KEY (file_id) REFERENCES naturadapt_files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE naturadapt_discussion_message_file');
    }
}
