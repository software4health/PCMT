<?php
declare(strict_types=1);
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManager;

class SeleniumBaseContext extends MinkContext implements Context
{
    /** @var AppKernel  */
    private $kernel;

    public function __construct()
    {
        $this->kernel = new AppKernel('dev', true);
        $this->kernel->boot();
    }

    /**
     * @Given I log in as a test user
     */
    public function logInAsATestUser(): void
    {
        $this->iAmOnHomepage();
        $this->fillField('_username', 'admin');
        $this->fillField('_password', 'Admin123');
        $this->pressButton('_submit');

        $this->getSession()->wait('5000');
    }

    /**
     * @Then /^wait for the page to load$/
     */
    public function waitForThePageToLoad()
    {
        $this->getSession()->wait('2000');
    }

    protected function purgeDatabase(string $entityClassName): void
    {
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
        $classMetaData = $em->getClassMetadata($entityClassName);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $query = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
            $connection->executeUpdate($query);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
        }
    }

    protected function getTestEntityManager():EntityManager
    {
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    }
}