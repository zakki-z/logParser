<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909140330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log_summaries (id SERIAL NOT NULL, date DATE NOT NULL, channel VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, count INT NOT NULL, sample_information TEXT DEFAULT NULL, first_occurrence TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_occurrence TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE log_entries ADD filename VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD file_hash VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE log_entries ADD file_size INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE log_summaries');
        $this->addSql('ALTER TABLE log_entries DROP filename');
        $this->addSql('ALTER TABLE log_entries DROP file_hash');
        $this->addSql('ALTER TABLE log_entries DROP uploaded_at');
        $this->addSql('ALTER TABLE log_entries DROP file_size');
    }
}
