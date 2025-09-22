<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916143251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file_history (id UUID NOT NULL, file_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7CDCC97093CB796C ON file_history (file_id)');
        $this->addSql('COMMENT ON COLUMN file_history.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN file_history.file_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE file_history ADD CONSTRAINT FK_7CDCC97093CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE file_history DROP CONSTRAINT FK_7CDCC97093CB796C');
        $this->addSql('DROP TABLE file_history');
    }
}
