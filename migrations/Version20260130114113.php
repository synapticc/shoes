<?php

// migrations/Version20260130114113.php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130114113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_color RENAME COLUMN fabrics TO fabric');
        $this->addSql('ALTER TABLE product_color_tags RENAME COLUMN tags TO tag');
        $this->addSql('ALTER TABLE product_color_texture RENAME COLUMN textures TO texture');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_color RENAME COLUMN fabric TO fabrics');
        $this->addSql('ALTER TABLE product_color_tags RENAME COLUMN tag TO tags');
        $this->addSql('ALTER TABLE product_color_texture RENAME COLUMN texture TO textures');
    }
}
