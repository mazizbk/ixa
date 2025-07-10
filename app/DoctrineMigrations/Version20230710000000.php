<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20230710000000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE montgolfiere_campaign_participation ADD rpsAlert TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE montgolfiere_campaign_participation DROP rpsAlert');
    }
}
