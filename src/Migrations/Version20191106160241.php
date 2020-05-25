<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191106160241 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sub_vacation ADD consultant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sub_vacation ADD CONSTRAINT FK_5975312C44F779A2 FOREIGN KEY (consultant_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5975312C44F779A2 ON sub_vacation (consultant_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sub_vacation DROP FOREIGN KEY FK_5975312C44F779A2');
        $this->addSql('DROP INDEX IDX_5975312C44F779A2 ON sub_vacation');
        $this->addSql('ALTER TABLE sub_vacation DROP consultant_id');
    }
}
