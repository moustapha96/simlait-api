<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220820120035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE laiterie DROP FOREIGN KEY FK_3E64CB6798260155');
        $this->addSql('ALTER TABLE laiterie DROP FOREIGN KEY FK_3E64CB67B8E6CFAF');
        $this->addSql('ALTER TABLE laiterie DROP FOREIGN KEY FK_3E64CB679F2C3FAB');
        $this->addSql('ALTER TABLE laiterie DROP FOREIGN KEY FK_3E64CB67CCF9E01E');
        $this->addSql('DROP INDEX IDX_3E64CB67CCF9E01E ON laiterie');
        $this->addSql('DROP INDEX IDX_3E64CB67B8E6CFAF ON laiterie');
        $this->addSql('DROP INDEX IDX_3E64CB6798260155 ON laiterie');
        $this->addSql('DROP INDEX IDX_3E64CB679F2C3FAB ON laiterie');
        $this->addSql('ALTER TABLE laiterie DROP region_id, DROP departement_id, DROP zone_id, DROP user_mobile_id, DROP nom, DROP telephone, DROP email, DROP created_at, DROP latitude, DROP longitude, DROP adresse, DROP is_synchrone, DROP is_certified, DROP prenom_proprietaire, DROP nom_proprietaire');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F84457598260155');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F844575CCF9E01E');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F8445759F2C3FAB');
        $this->addSql('DROP INDEX IDX_5F844575CCF9E01E ON transformateur');
        $this->addSql('DROP INDEX IDX_5F84457598260155 ON transformateur');
        $this->addSql('DROP INDEX IDX_5F8445759F2C3FAB ON transformateur');
        $this->addSql('ALTER TABLE transformateur DROP region_id, DROP departement_id, DROP zone_id, DROP prenom, DROP nom, DROP telephone, DROP denomination, DROP date_creation, DROP type_produit, DROP adresse, DROP observation, DROP site_production, DROP is_synchro, DROP is_certifed, DROP is_deleted, DROP raison_sociale, DROP age');
        $this->addSql('ALTER TABLE transformateur_demande DROP FOREIGN KEY FK_9F3DFD2653A093AD');
        $this->addSql('ALTER TABLE transformateur_demande DROP FOREIGN KEY FK_9F3DFD26CD11A2CF');
        $this->addSql('DROP INDEX IDX_9F3DFD26CD11A2CF ON transformateur_demande');
        $this->addSql('DROP INDEX IDX_9F3DFD2653A093AD ON transformateur_demande');
        $this->addSql('ALTER TABLE transformateur_demande DROP transformateur_id, DROP produits_id, DROP date_debut, DROP date_fin, DROP statut, DROP besoin');
        $this->addSql('ALTER TABLE transformateur_demande_suivi DROP FOREIGN KEY FK_31DB8B653A35C95F');
        $this->addSql('ALTER TABLE transformateur_demande_suivi DROP FOREIGN KEY FK_31DB8B65F49DCC2D');
        $this->addSql('DROP INDEX IDX_31DB8B653A35C95F ON transformateur_demande_suivi');
        $this->addSql('DROP INDEX IDX_31DB8B65F49DCC2D ON transformateur_demande_suivi');
        $this->addSql('ALTER TABLE transformateur_demande_suivi DROP laiteries_id, DROP demandes_id, DROP date, DROP message, DROP observation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE laiterie ADD region_id INT DEFAULT NULL, ADD departement_id INT DEFAULT NULL, ADD zone_id INT DEFAULT NULL, ADD user_mobile_id INT DEFAULT NULL, ADD nom VARCHAR(255) NOT NULL, ADD telephone VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD latitude VARCHAR(255) NOT NULL, ADD longitude VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) NOT NULL, ADD is_synchrone TINYINT(1) DEFAULT NULL, ADD is_certified TINYINT(1) DEFAULT NULL, ADD prenom_proprietaire VARCHAR(255) NOT NULL, ADD nom_proprietaire VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE laiterie ADD CONSTRAINT FK_3E64CB6798260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE laiterie ADD CONSTRAINT FK_3E64CB67B8E6CFAF FOREIGN KEY (user_mobile_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE laiterie ADD CONSTRAINT FK_3E64CB679F2C3FAB FOREIGN KEY (zone_id) REFERENCES zones (id)');
        $this->addSql('ALTER TABLE laiterie ADD CONSTRAINT FK_3E64CB67CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('CREATE INDEX IDX_3E64CB67CCF9E01E ON laiterie (departement_id)');
        $this->addSql('CREATE INDEX IDX_3E64CB67B8E6CFAF ON laiterie (user_mobile_id)');
        $this->addSql('CREATE INDEX IDX_3E64CB6798260155 ON laiterie (region_id)');
        $this->addSql('CREATE INDEX IDX_3E64CB679F2C3FAB ON laiterie (zone_id)');
        $this->addSql('ALTER TABLE transformateur ADD region_id INT DEFAULT NULL, ADD departement_id INT DEFAULT NULL, ADD zone_id INT DEFAULT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD nom VARCHAR(255) NOT NULL, ADD telephone VARCHAR(255) NOT NULL, ADD denomination VARCHAR(255) NOT NULL, ADD date_creation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', ADD type_produit VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) NOT NULL, ADD observation VARCHAR(255) NOT NULL, ADD site_production VARCHAR(255) NOT NULL, ADD is_synchro TINYINT(1) NOT NULL, ADD is_certifed TINYINT(1) NOT NULL, ADD is_deleted TINYINT(1) NOT NULL, ADD raison_sociale VARCHAR(255) NOT NULL, ADD age INT NOT NULL');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F84457598260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F844575CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F8445759F2C3FAB FOREIGN KEY (zone_id) REFERENCES zones (id)');
        $this->addSql('CREATE INDEX IDX_5F844575CCF9E01E ON transformateur (departement_id)');
        $this->addSql('CREATE INDEX IDX_5F84457598260155 ON transformateur (region_id)');
        $this->addSql('CREATE INDEX IDX_5F8445759F2C3FAB ON transformateur (zone_id)');
        $this->addSql('ALTER TABLE transformateur_demande ADD transformateur_id INT DEFAULT NULL, ADD produits_id INT DEFAULT NULL, ADD date_debut DATE NOT NULL, ADD date_fin DATE NOT NULL, ADD statut VARCHAR(255) NOT NULL, ADD besoin VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transformateur_demande ADD CONSTRAINT FK_9F3DFD2653A093AD FOREIGN KEY (transformateur_id) REFERENCES transformateur (id)');
        $this->addSql('ALTER TABLE transformateur_demande ADD CONSTRAINT FK_9F3DFD26CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('CREATE INDEX IDX_9F3DFD26CD11A2CF ON transformateur_demande (produits_id)');
        $this->addSql('CREATE INDEX IDX_9F3DFD2653A093AD ON transformateur_demande (transformateur_id)');
        $this->addSql('ALTER TABLE transformateur_demande_suivi ADD laiteries_id INT DEFAULT NULL, ADD demandes_id INT DEFAULT NULL, ADD date DATETIME NOT NULL, ADD message LONGTEXT NOT NULL, ADD observation LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE transformateur_demande_suivi ADD CONSTRAINT FK_31DB8B653A35C95F FOREIGN KEY (laiteries_id) REFERENCES laiterie (id)');
        $this->addSql('ALTER TABLE transformateur_demande_suivi ADD CONSTRAINT FK_31DB8B65F49DCC2D FOREIGN KEY (demandes_id) REFERENCES transformateur_demande (id)');
        $this->addSql('CREATE INDEX IDX_31DB8B653A35C95F ON transformateur_demande_suivi (laiteries_id)');
        $this->addSql('CREATE INDEX IDX_31DB8B65F49DCC2D ON transformateur_demande_suivi (demandes_id)');
    }
}
