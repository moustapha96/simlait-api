<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220816165320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_reset_password (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, code INT NOT NULL, date_create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_expirate DATETIME DEFAULT NULL, enable TINYINT(1) NOT NULL, INDEX IDX_F2A1FB1FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collecte (id INT AUTO_INCREMENT NOT NULL, produits_id INT DEFAULT NULL, conditionnements_id INT DEFAULT NULL, unites_id INT DEFAULT NULL, user_id INT DEFAULT NULL, emballages_id INT DEFAULT NULL, date_collecte DATETIME DEFAULT NULL, is_synchrone TINYINT(1) DEFAULT NULL, is_certified TINYINT(1) DEFAULT NULL, quantite DOUBLE PRECISION DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, is_deleted TINYINT(1) DEFAULT NULL, date_saisie DATE DEFAULT NULL, quantite_vendu INT DEFAULT NULL, quantite_autre INT DEFAULT NULL, quantite_perdu INT DEFAULT NULL, INDEX IDX_55AE4A3DCD11A2CF (produits_id), INDEX IDX_55AE4A3D5FC63A0C (conditionnements_id), INDEX IDX_55AE4A3DA6998D31 (unites_id), INDEX IDX_55AE4A3DA76ED395 (user_id), INDEX IDX_55AE4A3D28B35604 (emballages_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conditionnements (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, description VARCHAR(200) DEFAULT NULL, statut TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conditionnements_produits_unites (id INT AUTO_INCREMENT NOT NULL, produits_id INT DEFAULT NULL, conditionnements_id INT DEFAULT NULL, unites_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_855363CCCD11A2CF (produits_id), INDEX IDX_855363CC5FC63A0C (conditionnements_id), INDEX IDX_855363CCA6998D31 (unites_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, zones_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_C1765B6398260155 (region_id), INDEX IDX_C1765B63A6EAEB7A (zones_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emballage (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, statut TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE greeting (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logger (id INT AUTO_INCREMENT NOT NULL, host VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, date_request DATETIME DEFAULT NULL, statut_code VARCHAR(255) NOT NULL, response_content LONGTEXT DEFAULT NULL, method VARCHAR(255) DEFAULT NULL, request_uri VARCHAR(255) DEFAULT NULL, request_content LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', from_app VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, titre VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_read TINYINT(1) NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produits (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, description VARCHAR(200) DEFAULT NULL, statut TINYINT(1) DEFAULT NULL, unite VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BE2DDF8C6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produits_conditionnements (produits_id INT NOT NULL, conditionnements_id INT NOT NULL, INDEX IDX_D6D37BB0CD11A2CF (produits_id), INDEX IDX_D6D37BB05FC63A0C (conditionnements_id), PRIMARY KEY(produits_id, conditionnements_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produits_profils (produits_id INT NOT NULL, profils_id INT NOT NULL, INDEX IDX_4C11BD87CD11A2CF (produits_id), INDEX IDX_4C11BD87B9881AFB (profils_id), PRIMARY KEY(produits_id, profils_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profils (id INT NOT NULL, nom VARCHAR(255) NOT NULL, denomination VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transformateur (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, departement_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, prenom VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, denomination VARCHAR(255) NOT NULL, date_creation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', type_produit VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, observation VARCHAR(255) NOT NULL, site_production VARCHAR(255) NOT NULL, is_synchro TINYINT(1) NOT NULL, is_certifed TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, raison_sociale VARCHAR(255) NOT NULL, age INT NOT NULL, INDEX IDX_5F84457598260155 (region_id), INDEX IDX_5F844575CCF9E01E (departement_id), INDEX IDX_5F8445759F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transformateur_demande (id INT AUTO_INCREMENT NOT NULL, transformateur_id INT DEFAULT NULL, produits_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, statut VARCHAR(255) NOT NULL, besoin VARCHAR(255) NOT NULL, INDEX IDX_9F3DFD2653A093AD (transformateur_id), INDEX IDX_9F3DFD26CD11A2CF (produits_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transformateur_demande_suivi (id INT AUTO_INCREMENT NOT NULL, unites_id INT DEFAULT NULL, demandes_id INT DEFAULT NULL, date DATETIME NOT NULL, message LONGTEXT NOT NULL, observation LONGTEXT NOT NULL, INDEX IDX_31DB8B65A6998D31 (unites_id), INDEX IDX_31DB8B65F49DCC2D (demandes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unites (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, departement_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, user_mobile_id INT DEFAULT NULL, profil_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', latitude VARCHAR(255) NOT NULL, longitude VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, is_synchrone TINYINT(1) DEFAULT NULL, is_certified TINYINT(1) DEFAULT NULL, prenom_proprietaire VARCHAR(255) NOT NULL, nom_proprietaire VARCHAR(255) NOT NULL, localite VARCHAR(255) DEFAULT NULL, INDEX IDX_87F339C98260155 (region_id), INDEX IDX_87F339CCCF9E01E (departement_id), INDEX IDX_87F339C9F2C3FAB (zone_id), INDEX IDX_87F339CB8E6CFAF (user_mobile_id), INDEX IDX_87F339C275ED078 (profil_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unites_user (id INT AUTO_INCREMENT NOT NULL, unites_id INT DEFAULT NULL, user_mobile_id INT DEFAULT NULL, INDEX IDX_B1AA9E69A6998D31 (unites_id), INDEX IDX_B1AA9E69B8E6CFAF (user_mobile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, enabled TINYINT(1) DEFAULT NULL, phone VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, last_activity_at DATETIME DEFAULT NULL, is_active_now TINYINT(1) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, sexe VARCHAR(255) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, avatar VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_mobile (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, departement_id INT DEFAULT NULL, profil_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, sexe VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, statut VARCHAR(255) DEFAULT NULL, uuid VARCHAR(255) DEFAULT NULL, has_laiteries TINYINT(1) NOT NULL, localite VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_592138DDE7927C74 (email), INDEX IDX_592138DD98260155 (region_id), INDEX IDX_592138DDCCF9E01E (departement_id), INDEX IDX_592138DD275ED078 (profil_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vente (id INT AUTO_INCREMENT NOT NULL, produits_id INT DEFAULT NULL, conditionnements_id INT DEFAULT NULL, unites_id INT DEFAULT NULL, user_id INT DEFAULT NULL, emballages_id INT DEFAULT NULL, date_vente DATETIME DEFAULT NULL, is_synchrone TINYINT(1) DEFAULT NULL, is_certified TINYINT(1) DEFAULT NULL, quantite DOUBLE PRECISION DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, is_deleted TINYINT(1) DEFAULT NULL, INDEX IDX_888A2A4CCD11A2CF (produits_id), INDEX IDX_888A2A4C5FC63A0C (conditionnements_id), INDEX IDX_888A2A4CA6998D31 (unites_id), INDEX IDX_888A2A4CA76ED395 (user_id), INDEX IDX_888A2A4C28B35604 (emballages_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zones (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, statut TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code_reset_password ADD CONSTRAINT FK_F2A1FB1FA76ED395 FOREIGN KEY (user_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3DCD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D5FC63A0C FOREIGN KEY (conditionnements_id) REFERENCES conditionnements (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3DA6998D31 FOREIGN KEY (unites_id) REFERENCES unites (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3DA76ED395 FOREIGN KEY (user_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D28B35604 FOREIGN KEY (emballages_id) REFERENCES emballage (id)');
        $this->addSql('ALTER TABLE conditionnements_produits_unites ADD CONSTRAINT FK_855363CCCD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE conditionnements_produits_unites ADD CONSTRAINT FK_855363CC5FC63A0C FOREIGN KEY (conditionnements_id) REFERENCES conditionnements (id)');
        $this->addSql('ALTER TABLE conditionnements_produits_unites ADD CONSTRAINT FK_855363CCA6998D31 FOREIGN KEY (unites_id) REFERENCES unites (id)');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B6398260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B63A6EAEB7A FOREIGN KEY (zones_id) REFERENCES zones (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE produits_conditionnements ADD CONSTRAINT FK_D6D37BB0CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_conditionnements ADD CONSTRAINT FK_D6D37BB05FC63A0C FOREIGN KEY (conditionnements_id) REFERENCES conditionnements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_profils ADD CONSTRAINT FK_4C11BD87CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produits_profils ADD CONSTRAINT FK_4C11BD87B9881AFB FOREIGN KEY (profils_id) REFERENCES profils (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F84457598260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F844575CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE transformateur ADD CONSTRAINT FK_5F8445759F2C3FAB FOREIGN KEY (zone_id) REFERENCES zones (id)');
        $this->addSql('ALTER TABLE transformateur_demande ADD CONSTRAINT FK_9F3DFD2653A093AD FOREIGN KEY (transformateur_id) REFERENCES transformateur (id)');
        $this->addSql('ALTER TABLE transformateur_demande ADD CONSTRAINT FK_9F3DFD26CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE transformateur_demande_suivi ADD CONSTRAINT FK_31DB8B65A6998D31 FOREIGN KEY (unites_id) REFERENCES unites (id)');
        $this->addSql('ALTER TABLE transformateur_demande_suivi ADD CONSTRAINT FK_31DB8B65F49DCC2D FOREIGN KEY (demandes_id) REFERENCES transformateur_demande (id)');
        $this->addSql('ALTER TABLE unites ADD CONSTRAINT FK_87F339C98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE unites ADD CONSTRAINT FK_87F339CCCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE unites ADD CONSTRAINT FK_87F339C9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zones (id)');
        $this->addSql('ALTER TABLE unites ADD CONSTRAINT FK_87F339CB8E6CFAF FOREIGN KEY (user_mobile_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE unites ADD CONSTRAINT FK_87F339C275ED078 FOREIGN KEY (profil_id) REFERENCES profils (id)');
        $this->addSql('ALTER TABLE unites_user ADD CONSTRAINT FK_B1AA9E69A6998D31 FOREIGN KEY (unites_id) REFERENCES unites (id)');
        $this->addSql('ALTER TABLE unites_user ADD CONSTRAINT FK_B1AA9E69B8E6CFAF FOREIGN KEY (user_mobile_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE user_mobile ADD CONSTRAINT FK_592138DD98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user_mobile ADD CONSTRAINT FK_592138DDCCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE user_mobile ADD CONSTRAINT FK_592138DD275ED078 FOREIGN KEY (profil_id) REFERENCES profils (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CCD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4C5FC63A0C FOREIGN KEY (conditionnements_id) REFERENCES conditionnements (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CA6998D31 FOREIGN KEY (unites_id) REFERENCES unites (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CA76ED395 FOREIGN KEY (user_id) REFERENCES user_mobile (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4C28B35604 FOREIGN KEY (emballages_id) REFERENCES emballage (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D5FC63A0C');
        $this->addSql('ALTER TABLE conditionnements_produits_unites DROP FOREIGN KEY FK_855363CC5FC63A0C');
        $this->addSql('ALTER TABLE produits_conditionnements DROP FOREIGN KEY FK_D6D37BB05FC63A0C');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4C5FC63A0C');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F844575CCF9E01E');
        $this->addSql('ALTER TABLE unites DROP FOREIGN KEY FK_87F339CCCF9E01E');
        $this->addSql('ALTER TABLE user_mobile DROP FOREIGN KEY FK_592138DDCCF9E01E');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D28B35604');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4C28B35604');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3DCD11A2CF');
        $this->addSql('ALTER TABLE conditionnements_produits_unites DROP FOREIGN KEY FK_855363CCCD11A2CF');
        $this->addSql('ALTER TABLE produits_conditionnements DROP FOREIGN KEY FK_D6D37BB0CD11A2CF');
        $this->addSql('ALTER TABLE produits_profils DROP FOREIGN KEY FK_4C11BD87CD11A2CF');
        $this->addSql('ALTER TABLE transformateur_demande DROP FOREIGN KEY FK_9F3DFD26CD11A2CF');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4CCD11A2CF');
        $this->addSql('ALTER TABLE produits_profils DROP FOREIGN KEY FK_4C11BD87B9881AFB');
        $this->addSql('ALTER TABLE unites DROP FOREIGN KEY FK_87F339C275ED078');
        $this->addSql('ALTER TABLE user_mobile DROP FOREIGN KEY FK_592138DD275ED078');
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B6398260155');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F84457598260155');
        $this->addSql('ALTER TABLE unites DROP FOREIGN KEY FK_87F339C98260155');
        $this->addSql('ALTER TABLE user_mobile DROP FOREIGN KEY FK_592138DD98260155');
        $this->addSql('ALTER TABLE transformateur_demande DROP FOREIGN KEY FK_9F3DFD2653A093AD');
        $this->addSql('ALTER TABLE transformateur_demande_suivi DROP FOREIGN KEY FK_31DB8B65F49DCC2D');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3DA6998D31');
        $this->addSql('ALTER TABLE conditionnements_produits_unites DROP FOREIGN KEY FK_855363CCA6998D31');
        $this->addSql('ALTER TABLE transformateur_demande_suivi DROP FOREIGN KEY FK_31DB8B65A6998D31');
        $this->addSql('ALTER TABLE unites_user DROP FOREIGN KEY FK_B1AA9E69A6998D31');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4CA6998D31');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE92F8F78');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE code_reset_password DROP FOREIGN KEY FK_F2A1FB1FA76ED395');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3DA76ED395');
        $this->addSql('ALTER TABLE unites DROP FOREIGN KEY FK_87F339CB8E6CFAF');
        $this->addSql('ALTER TABLE unites_user DROP FOREIGN KEY FK_B1AA9E69B8E6CFAF');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4CA76ED395');
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B63A6EAEB7A');
        $this->addSql('ALTER TABLE transformateur DROP FOREIGN KEY FK_5F8445759F2C3FAB');
        $this->addSql('ALTER TABLE unites DROP FOREIGN KEY FK_87F339C9F2C3FAB');
        $this->addSql('DROP TABLE code_reset_password');
        $this->addSql('DROP TABLE collecte');
        $this->addSql('DROP TABLE conditionnements');
        $this->addSql('DROP TABLE conditionnements_produits_unites');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE emballage');
        $this->addSql('DROP TABLE greeting');
        $this->addSql('DROP TABLE logger');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE produits');
        $this->addSql('DROP TABLE produits_conditionnements');
        $this->addSql('DROP TABLE produits_profils');
        $this->addSql('DROP TABLE profils');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE transformateur');
        $this->addSql('DROP TABLE transformateur_demande');
        $this->addSql('DROP TABLE transformateur_demande_suivi');
        $this->addSql('DROP TABLE unites');
        $this->addSql('DROP TABLE unites_user');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_mobile');
        $this->addSql('DROP TABLE vente');
        $this->addSql('DROP TABLE zones');
    }
}
