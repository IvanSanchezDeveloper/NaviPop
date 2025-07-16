<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714195122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Created product entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app."product" (id SERIAL NOT NULL, user_seller_id INT NOT NULL, name VARCHAR(30) NOT NULL, image_path VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL , PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX UNIQ_IDENTIFIER_PRODUCT_SELLER ON app."product" (user_seller_id)');
        $this->addSql('CREATE INDEX UNIQ_IDENTIFIER_PRODUCT_NAME ON app."product" (name)');
        $this->addSql('CREATE INDEX UNIQ_IDENTIFIER_PRODUCT_PRICE ON app."product" (price)');
        $this->addSql('ALTER TABLE app."product" ADD CONSTRAINT FK_D34A04AD8D08AD3D FOREIGN KEY (user_seller_id) REFERENCES app."user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app."product" DROP CONSTRAINT FK_D34A04AD8D08AD3D');
        $this->addSql('DROP INDEX app.UNIQ_IDENTIFIER_PRODUCT_SELLER');
        $this->addSql('DROP INDEX app.UNIQ_IDENTIFIER_PRODUCT_NAME');
        $this->addSql('DROP INDEX app.UNIQ_IDENTIFIER_PRODUCT_PRICE');
        $this->addSql('DROP TABLE app."product"');
    }
}
