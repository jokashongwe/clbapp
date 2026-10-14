<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables annonce et annonce_lecture (annonces aux parents, lu / non lu).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE annonce (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(32) NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, cible_type VARCHAR(32) NOT NULL, cible_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', created_by_parent_id INT DEFAULT NULL, supp TINYINT(1) NOT NULL DEFAULT 0, INDEX IDX_ANNONCE_CIBLE (cible_type, cible_id), INDEX IDX_ANNONCE_CREATED (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('CREATE TABLE annonce_lecture (id INT AUTO_INCREMENT NOT NULL, annonce_id INT NOT NULL, parent_id INT NOT NULL, read_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_ANNONCE_LECTURE (annonce_id, parent_id), INDEX IDX_ANNONCE_LECTURE_PARENT (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_ANNONCE_CREATED_BY FOREIGN KEY (created_by_parent_id) REFERENCES parent_eleve (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE annonce_lecture ADD CONSTRAINT FK_ANNONCE_LECTURE_ANNONCE FOREIGN KEY (annonce_id) REFERENCES annonce (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE annonce_lecture ADD CONSTRAINT FK_ANNONCE_LECTURE_PARENT FOREIGN KEY (parent_id) REFERENCES parent_eleve (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE annonce_lecture DROP FOREIGN KEY FK_ANNONCE_LECTURE_ANNONCE');
        $this->addSql('ALTER TABLE annonce_lecture DROP FOREIGN KEY FK_ANNONCE_LECTURE_PARENT');
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_ANNONCE_CREATED_BY');
        $this->addSql('DROP TABLE annonce_lecture');
        $this->addSql('DROP TABLE annonce');
    }
}
