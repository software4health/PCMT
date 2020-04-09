<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\FunctionalTests;

use Behat\Behat\Context\Context;

class WebContext extends \SeleniumBaseContext implements Context
{
    /** @var int */
    private $numberOfResults = 0;

    /**
     * @Given There are :num products
     */
    public function thereIsQuantityOfProducts(int $quantity): void
    {
        $em = $this->getEntityManager();
        $repo = $em->getRepository(\Akeneo\Pim\Enrichment\Component\Product\Model\Product::class);
        $products = $repo->findBy([], null, $quantity);
        if (count($products) < $quantity) {
            throw new \Exception('Too few products on the list.');
        }
    }

    /**
     * @When I check :num products
     */
    public function iCheckProducts(int $num): void
    {
        $checkboxes = $this->getSession()->getPage()
            ->findAll('css', 'td.AknGrid-bodyCell > input[type=checkbox]');
        for ($i = 0; $i < $num; $i++) {
            $checkbox = $checkboxes[$i];
            $checkbox->click();
        }
    }

    /**
     * @When I click delete
     */
    public function iClickDelete(): void
    {
        $this->getSession()->wait('200');
        $buttons = $this->getSession()->getPage()
            ->findAll('css', 'a.AknButton--important');
        foreach ($buttons as $button) {
            if ('Delete' === $button->getText()) {
                break;
            }
        }
        if (empty($button)) {
            throw new \Exception('No delete button.');
        }
        $button->click();
    }

    /**
     * @When I confirm delete
     */
    public function iConfirmDelete(): void
    {
        $this->getSession()->wait('200');
        $buttons = $this->getSession()->getPage()
            ->findAll('css', 'div.AknButton--important');
        foreach ($buttons as $button) {
            if ('Delete' === $button->getText()) {
                break;
            }
        }
        if (empty($button)) {
            throw new \Exception('No delete confirmation button.');
        }
        $button->click();
    }

    /**
     * @Then the number of results should be lower by :quantity, try :attempts times
     */
    public function theNumberOfResultsShouldBeLowerBy(int $quantity, int $attempts): void
    {
        // we make a number of attempts, as the job in background may last for some time
        for ($i = 0; $i < $attempts; $i++) {
            $this->clickLink('Activity');
            $this->waitForThePageToLoad();
            $this->clickLink('Products');
            $this->waitForThePageToLoad();

            $previous = $this->numberOfResults;
            $newNumber = $this->getNumberOfResultsFromResultsPage();
            if ($previous - $quantity === $newNumber) {
                break;
            }
        }

        if ($previous - $quantity !== $newNumber) {
            throw new \Exception('Wrong number of results. Should be: ' . round($previous - $quantity));
        }
    }

    public function getNumberOfResultsFromResultsPage(): int
    {
        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', 'div.AknTitleContainer-title > div')
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    /**
     * @Given I read number of products
     */
    public function iReadNumberOfProducts(): void
    {
        $this->numberOfResults = $this->getNumberOfResultsFromResultsPage();
    }
}