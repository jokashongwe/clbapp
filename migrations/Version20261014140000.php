<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table parent_push_device (tokens FCM pour notifications push).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE parent_push_device (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, device_token VARCHAR(512) NOT NULL, platform VARCHAR(16) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_PARENT_PUSH_DEVICE_TOKEN (device_token), INDEX IDX_PARENT_PUSH_DEVICE_PARENT (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parent_push_device ADD CONSTRAINT FK_PARENT_PUSH_DEVICE_PARENT FOREIGN KEY (parent_id) REFERENCES parent_eleve (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parent_push_device DROP FOREIGN KEY FK_PARENT_PUSH_DEVICE_PARENT');
        $this->addSql('DROP TABLE parent_push_device');
    }
}
