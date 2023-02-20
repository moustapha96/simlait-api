<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220824082439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_mobile ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_mobile ADD CONSTRAINT FK_592138DD6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_592138DD6BF700BD ON user_mobile (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_mobile DROP FOREIGN KEY FK_592138DD6BF700BD');
        $this->addSql('DROP INDEX IDX_592138DD6BF700BD ON user_mobile');
        $this->addSql('ALTER TABLE user_mobile DROP status_id');
    }
}
