<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818191320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added the created_at property to Product entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            ALTER TABLE app."product"
            ADD COLUMN created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
        ');

        $this->addSql('
            CREATE INDEX IDX_PRODUCT_CREATED_AT ON app."product" (created_at)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX app.IDX_PRODUCT_CREATED_AT
        ');

        $this->addSql('
            ALTER TABLE app."product"
            DROP COLUMN created_at
        ');
    }
}
