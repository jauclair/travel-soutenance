<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190624124306 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE accommodation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, phone VARCHAR(10) NOT NULL, email VARCHAR(320) NOT NULL, adress VARCHAR(100) NOT NULL, country VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE accommodation_tour (accommodation_id INT NOT NULL, tour_id INT NOT NULL, INDEX IDX_63E93BA28F3692CD (accommodation_id), INDEX IDX_63E93BA215ED8D43 (tour_id), PRIMARY KEY(accommodation_id, tour_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(320) NOT NULL, password VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, gender VARCHAR(1) NOT NULL, phone VARCHAR(10) NOT NULL, mobile VARCHAR(10) DEFAULT NULL, email VARCHAR(320) NOT NULL, adress VARCHAR(500) NOT NULL, birthday DATE NOT NULL, participate TINYINT(1) NOT NULL, traveler_number INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flight (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(50) NOT NULL, flight_number VARCHAR(50) NOT NULL, departure_airport VARCHAR(150) NOT NULL, arrival_airport VARCHAR(150) NOT NULL, departure_date DATE NOT NULL, arrival_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, tour_id INT NOT NULL, total_price INT NOT NULL, INDEX IDX_F529939819EB6921 (client_id), INDEX IDX_F529939815ED8D43 (tour_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tour (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, departure_date DATE NOT NULL, arrival_date DATE NOT NULL, traveler_group INT NOT NULL, price INT NOT NULL, image VARCHAR(50) NOT NULL, INDEX IDX_6AD1F969F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tour_flight (tour_id INT NOT NULL, flight_id INT NOT NULL, INDEX IDX_F7D901E415ED8D43 (tour_id), INDEX IDX_F7D901E491F478C5 (flight_id), PRIMARY KEY(tour_id, flight_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accommodation_tour ADD CONSTRAINT FK_63E93BA28F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE accommodation_tour ADD CONSTRAINT FK_63E93BA215ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939815ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE tour_flight ADD CONSTRAINT FK_F7D901E415ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_flight ADD CONSTRAINT FK_F7D901E491F478C5 FOREIGN KEY (flight_id) REFERENCES flight (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE accommodation_tour DROP FOREIGN KEY FK_63E93BA28F3692CD');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939819EB6921');
        $this->addSql('ALTER TABLE tour DROP FOREIGN KEY FK_6AD1F969F92F3E70');
        $this->addSql('ALTER TABLE tour_flight DROP FOREIGN KEY FK_F7D901E491F478C5');
        $this->addSql('ALTER TABLE accommodation_tour DROP FOREIGN KEY FK_63E93BA215ED8D43');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939815ED8D43');
        $this->addSql('ALTER TABLE tour_flight DROP FOREIGN KEY FK_F7D901E415ED8D43');
        $this->addSql('DROP TABLE accommodation');
        $this->addSql('DROP TABLE accommodation_tour');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE flight');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE tour');
        $this->addSql('DROP TABLE tour_flight');
    }
}
