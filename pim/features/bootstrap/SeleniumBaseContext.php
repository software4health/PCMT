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

    public const WAIT_TIME_LONG = 5000;

    public const WAIT_TIME_MEDIUM = 4000;

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

        $this->getSession()->wait(self::WAIT_TIME_LONG);
    }

    /**
     * @Then /^wait for the page to load$/
     */
    public function waitForThePageToLoad(): void
    {
        $this->getSession()->wait(self::WAIT_TIME_MEDIUM);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

}