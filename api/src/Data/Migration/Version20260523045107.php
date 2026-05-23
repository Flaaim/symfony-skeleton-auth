<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523045107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_networks (id UUID NOT NULL, network VARCHAR(16) NOT NULL, identity VARCHAR(16) NOT NULL, user_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3934502BA76ED395 ON user_networks (user_id)');
        $this->addSql('CREATE UNIQUE INDEX network_identity_idx ON user_networks (network, identity)');
        $this->addSql('ALTER TABLE user_networks ADD CONSTRAINT FK_3934502BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_networks DROP CONSTRAINT FK_3934502BA76ED395');
        $this->addSql('DROP TABLE user_networks');
    }
}
