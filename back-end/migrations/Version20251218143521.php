<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218143521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_rights (id INT AUTO_INCREMENT NOT NULL, granted_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, cursus_id INT DEFAULT NULL, lesson_id INT DEFAULT NULL, purchase_id INT DEFAULT NULL, INDEX IDX_F3EB4431A76ED395 (user_id), INDEX IDX_F3EB443140AEF4B9 (cursus_id), INDEX IDX_F3EB4431CDF80196 (lesson_id), INDEX IDX_F3EB4431558FBEB9 (purchase_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE certifications (id INT AUTO_INCREMENT NOT NULL, validated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_3B0D76D5A76ED395 (user_id), INDEX IDX_3B0D76D559027487 (theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cursus (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price INT NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, theme_id INT NOT NULL, INDEX IDX_255A0C359027487 (theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cursus_validations (id INT AUTO_INCREMENT NOT NULL, validated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, cursus_id INT NOT NULL, INDEX IDX_482348D3A76ED395 (user_id), INDEX IDX_482348D340AEF4B9 (cursus_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson_validations (id INT AUTO_INCREMENT NOT NULL, validated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_40CBC404A76ED395 (user_id), INDEX IDX_40CBC404CDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lessons (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, video_url VARCHAR(255) DEFAULT NULL, position INT NOT NULL, price INT NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, cursus_id INT NOT NULL, INDEX IDX_3F4218D940AEF4B9 (cursus_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchases (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, currency VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, cursus_id INT NOT NULL, lesson_id INT NOT NULL, UNIQUE INDEX UNIQ_AA6431FE1A314A57 (stripe_session_id), INDEX IDX_AA6431FEA76ED395 (user_id), INDEX IDX_AA6431FE40AEF4B9 (cursus_id), INDEX IDX_AA6431FECDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE themes (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, UNIQUE INDEX UNIQ_154232DE989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles JSON NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, is_verified TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE access_rights ADD CONSTRAINT FK_F3EB4431A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE access_rights ADD CONSTRAINT FK_F3EB443140AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE access_rights ADD CONSTRAINT FK_F3EB4431CDF80196 FOREIGN KEY (lesson_id) REFERENCES lessons (id)');
        $this->addSql('ALTER TABLE access_rights ADD CONSTRAINT FK_F3EB4431558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchases (id)');
        $this->addSql('ALTER TABLE certifications ADD CONSTRAINT FK_3B0D76D5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE certifications ADD CONSTRAINT FK_3B0D76D559027487 FOREIGN KEY (theme_id) REFERENCES themes (id)');
        $this->addSql('ALTER TABLE cursus ADD CONSTRAINT FK_255A0C359027487 FOREIGN KEY (theme_id) REFERENCES themes (id)');
        $this->addSql('ALTER TABLE cursus_validations ADD CONSTRAINT FK_482348D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE cursus_validations ADD CONSTRAINT FK_482348D340AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE lesson_validations ADD CONSTRAINT FK_40CBC404A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE lesson_validations ADD CONSTRAINT FK_40CBC404CDF80196 FOREIGN KEY (lesson_id) REFERENCES lessons (id)');
        $this->addSql('ALTER TABLE lessons ADD CONSTRAINT FK_3F4218D940AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FE40AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT FK_AA6431FECDF80196 FOREIGN KEY (lesson_id) REFERENCES lessons (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_rights DROP FOREIGN KEY FK_F3EB4431A76ED395');
        $this->addSql('ALTER TABLE access_rights DROP FOREIGN KEY FK_F3EB443140AEF4B9');
        $this->addSql('ALTER TABLE access_rights DROP FOREIGN KEY FK_F3EB4431CDF80196');
        $this->addSql('ALTER TABLE access_rights DROP FOREIGN KEY FK_F3EB4431558FBEB9');
        $this->addSql('ALTER TABLE certifications DROP FOREIGN KEY FK_3B0D76D5A76ED395');
        $this->addSql('ALTER TABLE certifications DROP FOREIGN KEY FK_3B0D76D559027487');
        $this->addSql('ALTER TABLE cursus DROP FOREIGN KEY FK_255A0C359027487');
        $this->addSql('ALTER TABLE cursus_validations DROP FOREIGN KEY FK_482348D3A76ED395');
        $this->addSql('ALTER TABLE cursus_validations DROP FOREIGN KEY FK_482348D340AEF4B9');
        $this->addSql('ALTER TABLE lesson_validations DROP FOREIGN KEY FK_40CBC404A76ED395');
        $this->addSql('ALTER TABLE lesson_validations DROP FOREIGN KEY FK_40CBC404CDF80196');
        $this->addSql('ALTER TABLE lessons DROP FOREIGN KEY FK_3F4218D940AEF4B9');
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FEA76ED395');
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FE40AEF4B9');
        $this->addSql('ALTER TABLE purchases DROP FOREIGN KEY FK_AA6431FECDF80196');
        $this->addSql('DROP TABLE access_rights');
        $this->addSql('DROP TABLE certifications');
        $this->addSql('DROP TABLE cursus');
        $this->addSql('DROP TABLE cursus_validations');
        $this->addSql('DROP TABLE lesson_validations');
        $this->addSql('DROP TABLE lessons');
        $this->addSql('DROP TABLE purchases');
        $this->addSql('DROP TABLE themes');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
