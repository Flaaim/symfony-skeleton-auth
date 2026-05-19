<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519002220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (password_hash VARCHAR(255) DEFAULT NULL, new_email VARCHAR DEFAULT NULL, role VARCHAR NOT NULL, id VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR NOT NULL, status VARCHAR NOT NULL, join_confirm_token_value VARCHAR(255) DEFAULT NULL, join_confirm_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, password_reset_token_value VARCHAR(255) DEFAULT NULL, password_reset_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, new_email_token_value VARCHAR(255) DEFAULT NULL, new_email_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
