<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729233346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(2) DEFAULT NULL, lat DOUBLE PRECISION NOT NULL, lon DOUBLE PRECISION NOT NULL, pl VARCHAR(255) DEFAULT NULL, en VARCHAR(255) DEFAULT NULL, temp_c INT DEFAULT NULL, temp_f INT DEFAULT NULL, icon VARCHAR(3) DEFAULT NULL, def TINYINT(1) NOT NULL, has_user TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cities_user (cities_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_AF040A44CAC75398 (cities_id), INDEX IDX_AF040A44A76ED395 (user_id), PRIMARY KEY(cities_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, username VARCHAR(180) NOT NULL, is_verified TINYINT(1) NOT NULL, user_city INT NOT NULL, locale VARCHAR(2) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cities_user ADD CONSTRAINT FK_AF040A44CAC75398 FOREIGN KEY (cities_id) REFERENCES cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cities_user ADD CONSTRAINT FK_AF040A44A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('INSERT INTO cities (name, country, lat, lon, pl, en, temp_c, icon, temp_f, def, has_user) VALUES
            ("London", "GB", 51.5073219, -0.1276474, "Londyn", "London", 16, "03d", 66, 1, 0),
            ("Tokyo", "JP", 35.6828387, 139.7594549, "Tokio", "Tokyo", 29, "03d", 83, 1, 0),
            ("New York", "US", 40.7127281, -74.0060152, "Nowy Jork", "New York", 26, "03d", 72, 1, 0),
            ("Paris", "FR", 48.8588897, 2.3200410217201, "Paryż", NULL, 19, "01n", 66, 1, 0),
            ("Shanghai", "CN", 31.2322758, 121.4692071, "Szanghaj", "Shanghai", 28, "01d", 102, 1, 0),
            ("Istanbul", "TR", 41.0096334, 28.9651646, "Stambuł", "Istanbul", 23, "01n", 66, 1, 0),
            ("Buenos Aires", "AR", -34.6075682, -58.4370894, "Buenos Aires", "Buenos Aires", 16, "03d", 46, 1, 0),
            ("Mexico City", "MX", 19.4326296, -99.1331785, "Meksyk", "Mexico City", 14, "11d", 66, 1, 0),
            ("Cairo", "EG", 30.0443879, 31.2357257, "Kair", "Cairo", 28, "01n", 76, 1, 0),
            ("Delhi", "IN", 28.6517178, 77.2219388, NULL, "Delhi", 28, "50n", 84, 1, 0),
            ("Madrid", "ES", 40.4167047, -3.7035825, "Madryt", "Madrid", 28, "01n", 79, 1, 0),
            ("Moscow", "RU", 55.7504461, 37.6174943, "Moskwa", "Moscow", 19, "03d", 70, 1, 0),
            ("Miami", "US", 25.7741728, -80.19362, "Miami", "Miami", 28, "02n", 82, 1, 0),
            ("Singapore", "SG", 1.2904753, 103.8520359, "Singapur", "Singapore", 27, "03d", 87, 1, 0),
            ("Kinshasa", "CD", -4.3217055, 15.3125974, "Kinszasa", "Kinshasa", 23, "03d", 68, 1, 0);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cities_user DROP FOREIGN KEY FK_AF040A44CAC75398');
        $this->addSql('ALTER TABLE cities_user DROP FOREIGN KEY FK_AF040A44A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE cities_user');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
