<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191028123656 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project_days (id INT AUTO_INCREMENT NOT NULL, monthly_summary_id INT DEFAULT NULL, projects_id INT DEFAULT NULL, days INT DEFAULT NULL, INDEX IDX_D275347EE3DFB4D9 (monthly_summary_id), INDEX IDX_D275347E1EDE0F55 (projects_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_days ADD CONSTRAINT FK_D275347EE3DFB4D9 FOREIGN KEY (monthly_summary_id) REFERENCES monthly_summary (id)');
        $this->addSql('ALTER TABLE project_days ADD CONSTRAINT FK_D275347E1EDE0F55 FOREIGN KEY (projects_id) REFERENCES project (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE project_days');
    }
}
