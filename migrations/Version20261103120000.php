<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables super_user_pin et super_user_session (auth sans parent_eleve).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE super_user_pin (id INT AUTO_INCREMENT NOT NULL, telephone VARCHAR(32) NOT NULL, pin_hash VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_SUPER_USER_PIN_TELEPHONE (telephone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE super_user_session (id INT AUTO_INCREMENT NOT NULL, telephone VARCHAR(32) NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_SUPER_USER_SESSION_TOKEN (token_hash), INDEX IDX_SUPER_USER_SESSION_TELEPHONE (telephone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE super_user_session');
        $this->addSql('DROP TABLE super_user_pin');
    }
}
