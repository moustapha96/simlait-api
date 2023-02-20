<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220824134409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unites_demande ADD user_autre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unites_demande ADD CONSTRAINT FK_797CDC7CA1E783AB FOREIGN KEY (user_autre_id) REFERENCES user_autre (id)');
        $this->addSql('CREATE INDEX IDX_797CDC7CA1E783AB ON unites_demande (user_autre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unites_demande DROP FOREIGN KEY FK_797CDC7CA1E783AB');
        $this->addSql('DROP INDEX IDX_797CDC7CA1E783AB ON unites_demande');
        $this->addSql('ALTER TABLE unites_demande DROP user_autre_id');
    }
}
