<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191028124042 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_days DROP FOREIGN KEY FK_D275347E1EDE0F55');
        $this->addSql('DROP INDEX IDX_D275347E1EDE0F55 ON project_days');
        $this->addSql('ALTER TABLE project_days CHANGE projects_id project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_days ADD CONSTRAINT FK_D275347E166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_D275347E166D1F9C ON project_days (project_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_days DROP FOREIGN KEY FK_D275347E166D1F9C');
        $this->addSql('DROP INDEX IDX_D275347E166D1F9C ON project_days');
        $this->addSql('ALTER TABLE project_days CHANGE project_id projects_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_days ADD CONSTRAINT FK_D275347E1EDE0F55 FOREIGN KEY (projects_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_D275347E1EDE0F55 ON project_days (projects_id)');
    }
}
