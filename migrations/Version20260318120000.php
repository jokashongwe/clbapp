<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables parent_pin et parent_session liées à parent_eleve (authentification API).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE parent_pin (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, telephone VARCHAR(32) NOT NULL, pin_hash VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_PARENT_PIN_PARENT (parent_id), UNIQUE INDEX UNIQ_PARENT_PIN_TELEPHONE (telephone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parent_session (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_PARENT_SESSION_TOKEN (token_hash), INDEX IDX_PARENT_SESSION_PARENT (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parent_pin ADD CONSTRAINT FK_PARENT_PIN_PARENT FOREIGN KEY (parent_id) REFERENCES parent_eleve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parent_session ADD CONSTRAINT FK_PARENT_SESSION_PARENT FOREIGN KEY (parent_id) REFERENCES parent_eleve (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parent_pin DROP FOREIGN KEY FK_PARENT_PIN_PARENT');
        $this->addSql('ALTER TABLE parent_session DROP FOREIGN KEY FK_PARENT_SESSION_PARENT');
        $this->addSql('DROP TABLE parent_pin');
        $this->addSql('DROP TABLE parent_session');
    }
}
