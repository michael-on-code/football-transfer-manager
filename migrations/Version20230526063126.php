<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230526063126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD photo VARCHAR(255) DEFAULT NULL');
        $this->addSql("INSERT INTO `user` (`id`, `username`, `roles`, `password`, `email`, `photo`) VALUES
        (default, 'administrator', '[\"ROLE_ADMIN\"]', '$2y$13\$FxWcLO4NOopenCeuLVuFbu4kH01A49a/Fg8ajvCDOIin21a8otVuK', 'michaeloncode@gmail.com', '');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP photo');
    }
}
