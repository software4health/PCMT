<?php
declare(strict_types=1);
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;

class SeleniumBaseContext extends MinkContext implements Context
{
    /** @var AppKernel  */
    private $kernel;

    public function __construct()
    {
        $this->kernel = new AppKernel('test', true);
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
    public function waitForThePageToLoad(): void
    {
        $this->getSession()->wait('2000');
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

}