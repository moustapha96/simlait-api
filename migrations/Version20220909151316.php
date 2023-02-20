<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220909151316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unites_demande_suivi DROP FOREIGN KEY FK_EEA8350ABE3AE5D0');
        $this->addSql('DROP INDEX IDX_EEA8350ABE3AE5D0 ON unites_demande_suivi');
        $this->addSql('ALTER TABLE unites_demande_suivi DROP unites_autre_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unites_demande_suivi ADD unites_autre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unites_demande_suivi ADD CONSTRAINT FK_EEA8350ABE3AE5D0 FOREIGN KEY (unites_autre_id) REFERENCES unites_autre (id)');
        $this->addSql('CREATE INDEX IDX_EEA8350ABE3AE5D0 ON unites_demande_suivi (unites_autre_id)');
    }
}
