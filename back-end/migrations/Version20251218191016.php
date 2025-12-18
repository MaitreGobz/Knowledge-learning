<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218191016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rights CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE certifications CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE cursus CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE cursus_validations CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_validations CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE lessons CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE purchases CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE themes CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE created_by created_by VARCHAR(180) DEFAULT NULL, CHANGE updated_by updated_by VARCHAR(180) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rights CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE certifications CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cursus CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cursus_validations CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_validations CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lessons CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE purchases CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE themes CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE created_by created_by INT DEFAULT NULL, CHANGE updated_by updated_by INT DEFAULT NULL');
    }
}
