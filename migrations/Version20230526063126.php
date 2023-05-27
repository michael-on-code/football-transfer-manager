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
        $this->addSql("INSERT INTO `parameters` (`id`, `label`, `value`) VALUES
        (45, 'siteName', 'Transfer Manager'),
        (46, 'siteDescription', 'Imperdiet debitis in! Porttitor, repellendus harum pede! Eu amet aenean culpa, molestias. Quisquam doloremque optio varius. Malesuada. Quod, expedita animi.'),
        (47, 'siteCurrency', 'USD'),
        (48, 'siteLogo', '');");
    }#

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP photo');
    }
}
