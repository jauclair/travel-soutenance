<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190619095720 extends AbstractMigration
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
        $this->addSql('CREATE TABLE flight (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(50) NOT NULL, flight_number VARCHAR(50) NOT NULL, icao24 VARCHAR(6) NOT NULL, departure_airport VARCHAR(150) NOT NULL, arrival_airport VARCHAR(150) NOT NULL, departure_date DATE NOT NULL, arrival_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tour_flight (tour_id INT NOT NULL, flight_id INT NOT NULL, INDEX IDX_F7D901E415ED8D43 (tour_id), INDEX IDX_F7D901E491F478C5 (flight_id), PRIMARY KEY(tour_id, flight_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tour_accommodation (tour_id INT NOT NULL, accommodation_id INT NOT NULL, INDEX IDX_488A801115ED8D43 (tour_id), INDEX IDX_488A80118F3692CD (accommodation_id), PRIMARY KEY(tour_id, accommodation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tour_flight ADD CONSTRAINT FK_F7D901E415ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_flight ADD CONSTRAINT FK_F7D901E491F478C5 FOREIGN KEY (flight_id) REFERENCES flight (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_accommodation ADD CONSTRAINT FK_488A801115ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_accommodation ADD CONSTRAINT FK_488A80118F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tour_accommodation DROP FOREIGN KEY FK_488A80118F3692CD');
        $this->addSql('ALTER TABLE tour_flight DROP FOREIGN KEY FK_F7D901E491F478C5');
        $this->addSql('DROP TABLE accommodation');
        $this->addSql('DROP TABLE flight');
        $this->addSql('DROP TABLE tour_flight');
        $this->addSql('DROP TABLE tour_accommodation');
    }
}
