<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200629140506 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = "
            INSERT INTO akeneo_pim.pim_api_client (random_id, redirect_uris, secret, allowed_grant_types, label) 
            VALUES ('api_connection_1', 'a:0:{}', 'api_secret', 'a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}', 'API first connection');
        ";
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
