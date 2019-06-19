<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190619092919 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` ADD travel_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398ECAB15B3 FOREIGN KEY (travel_id) REFERENCES tour (id)');
        $this->addSql('CREATE INDEX IDX_F5299398ECAB15B3 ON `order` (travel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398ECAB15B3');
        $this->addSql('DROP INDEX IDX_F5299398ECAB15B3 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP travel_id');
    }
}
