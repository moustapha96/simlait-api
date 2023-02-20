<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220825152523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unites_autre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, adresse VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unites_demande DROP FOREIGN KEY FK_797CDC7CA1E783AB');
        $this->addSql('DROP INDEX IDX_797CDC7CA1E783AB ON unites_demande');
        $this->addSql('ALTER TABLE unites_demande CHANGE user_autre_id unites_autre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unites_demande ADD CONSTRAINT FK_797CDC7CBE3AE5D0 FOREIGN KEY (unites_autre_id) REFERENCES unites_autre (id)');
        $this->addSql('CREATE INDEX IDX_797CDC7CBE3AE5D0 ON unites_demande (unites_autre_id)');
        $this->addSql('ALTER TABLE user_autre DROP nom, DROP prenom, DROP telephone, DROP email, DROP adresse, DROP created_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unites_demande DROP FOREIGN KEY FK_797CDC7CBE3AE5D0');
        $this->addSql('DROP TABLE unites_autre');
        $this->addSql('DROP INDEX IDX_797CDC7CBE3AE5D0 ON unites_demande');
        $this->addSql('ALTER TABLE unites_demande CHANGE unites_autre_id user_autre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unites_demande ADD CONSTRAINT FK_797CDC7CA1E783AB FOREIGN KEY (user_autre_id) REFERENCES user_autre (id)');
        $this->addSql('CREATE INDEX IDX_797CDC7CA1E783AB ON unites_demande (user_autre_id)');
        $this->addSql('ALTER TABLE user_autre ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD telephone VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD adresse VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
