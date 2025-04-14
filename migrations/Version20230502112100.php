<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230502112100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
       // Created colum to false by default
       $this->addSql('UPDATE naturadapt_users SET has_adaptative_approach = 0');
       $this->addSql('UPDATE naturadapt_users SET has_been_notified_of_new_adaptative_approach = 0');

    }

    public function down(Schema $schema): void
    {
    }
}
