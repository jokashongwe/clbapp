<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table devoir (fichiers PDF/Word partagés par classe, stockage R2).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE devoir (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, storage_key VARCHAR(512) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(128) NOT NULL, file_size INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by_parent_id INT DEFAULT NULL, supp TINYINT(1) NOT NULL DEFAULT 0, INDEX IDX_DEVOIR_CLASSE (classe_id), INDEX IDX_DEVOIR_CREATED (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devoir ADD CONSTRAINT FK_DEVOIR_CLASSE FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE devoir ADD CONSTRAINT FK_DEVOIR_CREATED_BY FOREIGN KEY (created_by_parent_id) REFERENCES parent_eleve (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devoir DROP FOREIGN KEY FK_DEVOIR_CLASSE');
        $this->addSql('ALTER TABLE devoir DROP FOREIGN KEY FK_DEVOIR_CREATED_BY');
        $this->addSql('DROP TABLE devoir');
    }
}
