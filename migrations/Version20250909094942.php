<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909094942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_channel');
        $this->addSql('DROP INDEX idx_date');
        $this->addSql('DROP INDEX idx_type');
        $this->addSql('ALTER TABLE log_entries DROP name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE log_entries ADD name TEXT NOT NULL');
        $this->addSql('CREATE INDEX idx_channel ON log_entries (channel)');
        $this->addSql('CREATE INDEX idx_date ON log_entries (date)');
        $this->addSql('CREATE INDEX idx_type ON log_entries (type)');
    }
}
