<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191129121151 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sick_day ADD document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sick_day ADD CONSTRAINT FK_B0DE94F2C33F7837 FOREIGN KEY (document_id) REFERENCES my_document (id)');
        $this->addSql('CREATE INDEX IDX_B0DE94F2C33F7837 ON sick_day (document_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sick_day DROP FOREIGN KEY FK_B0DE94F2C33F7837');
        $this->addSql('DROP INDEX IDX_B0DE94F2C33F7837 ON sick_day');
        $this->addSql('ALTER TABLE sick_day DROP document_id');
    }
}
