<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191204152746 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE my_document DROP FOREIGN KEY FK_EA3B1F39CCD64A71');
        $this->addSql('CREATE TABLE load_initial_documents (id INT AUTO_INCREMENT NOT NULL, cv VARCHAR(255) DEFAULT NULL, carte_identite VARCHAR(255) DEFAULT NULL, rib VARCHAR(255) DEFAULT NULL, navigo VARCHAR(255) DEFAULT NULL, attestation_domicile VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE document_holder');
        $this->addSql('DROP INDEX IDX_EA3B1F39CCD64A71 ON my_document');
        $this->addSql('ALTER TABLE my_document DROP document_holder_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE document_holder (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE load_initial_documents');
        $this->addSql('ALTER TABLE my_document ADD document_holder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE my_document ADD CONSTRAINT FK_EA3B1F39CCD64A71 FOREIGN KEY (document_holder_id) REFERENCES document_holder (id)');
        $this->addSql('CREATE INDEX IDX_EA3B1F39CCD64A71 ON my_document (document_holder_id)');
    }
}
