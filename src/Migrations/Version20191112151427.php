<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191112151427 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE96861321');
        $this->addSql('DROP TABLE project_container');
        $this->addSql('ALTER TABLE vacation ADD granted_absence_days INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_2FB3D0EE96861321 ON project');
        $this->addSql('ALTER TABLE project DROP project_container_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project_container (id INT AUTO_INCREMENT NOT NULL, consultant_id INT DEFAULT NULL, INDEX IDX_D69EA9CC44F779A2 (consultant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE project_container ADD CONSTRAINT FK_D69EA9CC44F779A2 FOREIGN KEY (consultant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project ADD project_container_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE96861321 FOREIGN KEY (project_container_id) REFERENCES project_container (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE96861321 ON project (project_container_id)');
        $this->addSql('ALTER TABLE vacation DROP granted_absence_days');
    }
}
