<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190621082913 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE accommodation_tour (accommodation_id INT NOT NULL, tour_id INT NOT NULL, INDEX IDX_63E93BA28F3692CD (accommodation_id), INDEX IDX_63E93BA215ED8D43 (tour_id), PRIMARY KEY(accommodation_id, tour_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accommodation_tour ADD CONSTRAINT FK_63E93BA28F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE accommodation_tour ADD CONSTRAINT FK_63E93BA215ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE tour_accommodation');
        $this->addSql('ALTER TABLE client DROP street, DROP town, DROP zipcode, DROP country, CHANGE mobile mobile VARCHAR(10) DEFAULT NULL, CHANGE adress adress VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE flight DROP icao24');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398DC2902E0');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398ECAB15B3');
        $this->addSql('DROP INDEX IDX_F5299398DC2902E0 ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398ECAB15B3 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD client_id INT NOT NULL, DROP client_id_id, DROP travel_id, DROP airport_transfer, DROP cancel_insurance, DROP option_1, DROP option_2, DROP option_3');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_F529939819EB6921 ON `order` (client_id)');
        $this->addSql('ALTER TABLE tour DROP transfer_price, DROP cancel_price, DROP option_1_desc, DROP option_1_price, DROP option_2_desc, DROP option_2_price, DROP option_3_desc, DROP option_3_price, CHANGE traveler_group traveler_group INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tour_accommodation (tour_id INT NOT NULL, accommodation_id INT NOT NULL, INDEX IDX_488A80118F3692CD (accommodation_id), INDEX IDX_488A801115ED8D43 (tour_id), PRIMARY KEY(tour_id, accommodation_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tour_accommodation ADD CONSTRAINT FK_488A801115ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_accommodation ADD CONSTRAINT FK_488A80118F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodation (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE accommodation_tour');
        $this->addSql('ALTER TABLE client ADD street VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, ADD town VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, ADD zipcode VARCHAR(5) NOT NULL COLLATE utf8mb4_unicode_ci, ADD country VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE mobile mobile VARCHAR(10) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE adress adress VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE flight ADD icao24 VARCHAR(6) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939819EB6921');
        $this->addSql('DROP INDEX IDX_F529939819EB6921 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD travel_id INT NOT NULL, ADD airport_transfer SMALLINT NOT NULL, ADD cancel_insurance TINYINT(1) NOT NULL, ADD option_1 TINYINT(1) NOT NULL, ADD option_2 TINYINT(1) NOT NULL, ADD option_3 TINYINT(1) NOT NULL, CHANGE client_id client_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398DC2902E0 FOREIGN KEY (client_id_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398ECAB15B3 FOREIGN KEY (travel_id) REFERENCES tour (id)');
        $this->addSql('CREATE INDEX IDX_F5299398DC2902E0 ON `order` (client_id_id)');
        $this->addSql('CREATE INDEX IDX_F5299398ECAB15B3 ON `order` (travel_id)');
        $this->addSql('ALTER TABLE tour ADD transfer_price INT NOT NULL, ADD cancel_price INT NOT NULL, ADD option_1_desc VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD option_1_price INT NOT NULL, ADD option_2_desc VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD option_2_price INT NOT NULL, ADD option_3_desc VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD option_3_price INT NOT NULL, CHANGE traveler_group traveler_group TINYINT(1) NOT NULL');
    }
}
