<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915234906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8C9F3610A76ED395 ON file (user_id)');
        $this->addSql('ALTER TABLE log_entries ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log_entries DROP filename');
        $this->addSql('ALTER TABLE log_entries DROP file_hash');
        $this->addSql('ALTER TABLE log_entries DROP uploaded_at');
        $this->addSql('ALTER TABLE log_entries DROP file_size');
        $this->addSql('ALTER TABLE log_entries ADD CONSTRAINT FK_15358B5293CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_15358B5293CB796C ON log_entries (file_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE log_entries DROP CONSTRAINT FK_15358B5293CB796C');
        $this->addSql('DROP INDEX IDX_15358B5293CB796C');
        $this->addSql('ALTER TABLE log_entries ADD filename VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD file_hash VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD file_size INT NOT NULL');
        $this->addSql('ALTER TABLE log_entries DROP file_id');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610A76ED395');
        $this->addSql('DROP INDEX IDX_8C9F3610A76ED395');
        $this->addSql('ALTER TABLE file DROP user_id');
    }
}
