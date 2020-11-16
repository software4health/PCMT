<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\FunctionalTests;

use Behat\Behat\Context\Context;
use WebContentFinder;

class WebContext extends \SeleniumBaseContext implements Context
{
    /** @var int */
    private $numberOfResults;

    /**
     * @And I save
     * @When I save
     */
    public function iSave(): void
    {
        $this->waitForThePageToLoad();
        $locator = 'div.AknButton.ok';
        if (!$this->waitUntil(\WebContentFinder::SAVE_BUTTON_EXISTS, $locator)) {
            throw new \Exception('Element not found');
        }
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @When I fill in :field with timestamp
     */
    public function fillFieldWithTimestamp(string $field): void
    {
        $this->fillField($field, (new \DateTime())->getTimestamp());
    }

    /**
     * @Then I click on create rule
     */
    public function iClickOnCreateRule(): void
    {
        $this->waitUntil(WebContentFinder::ELEMENT_WITH_ID_EXISTS, 'create-button-extension');
        $buttonDiv = $this->getSession()->getPage()->findById('create-button-extension');
        if (!$buttonDiv) {
            throw new \Exception('Create rule button not found');
        }
        $buttonDiv->click();
        $this->waitForThePageToLoad();
    }

    /**
     * @Then I should get :count errors
     */
    public function iShouldGetErrors(int $count): void
    {
        $this->waitForThePageToLoad();
        $locators = 'span.error-message';
        $selectors = $this->getSession()->getPage()->findAll('css', $locators);
        if ($count !== count($selectors)) {
            throw new \Exception(
                sprintf('Wrong number of errors. Should be %d, in test: %d', $count, count($selectors))
            );
        }
    }

    /**
     * @When I choose :group option
     * @And I choose :group option
     */
    public function iChooseOption(string $option): void
    {
        $attributeGroupSelect = $this->getSession()->getPage()->findAll('css', '#select2-drop > ul > li');
        foreach ($attributeGroupSelect as $selectOption) {
            if ($selectOption->getText() === $option) {
                $selectOption->click();

                return;
            }
        }
        throw new \Exception('Did not find option matching to: ' . $option);
    }

    /**
     * @Given I wait
     */
    public function iWait(): void
    {
        $this->waitForThePageToLoad();
    }

    /**
     * @Given I should get success message
     */
    public function iShouldGetSuccessMessage(): void
    {
        $result = $this->waitUntil(WebContentFinder::FLASH_SUCCESS_MESSAGE_EXISTS);
        if (!$result) {
            throw new \Exception('Success message not found');
        }
    }

    /**
     * @Given I read number of rules
     */
    public function iReadNumberOfRules(): void
    {
        $this->waitToLoadPage('RULES');
        $this->numberOfResults = $this->getNumberOfResultsOnList();
    }

    public function getNumberOfResultsOnList(): int
    {
        $this->getSession()->wait(1000);

        $locator = 'div.AknTitleContainer-title > div';
        $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);

        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', $locator)
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    /**
     * @When I wait and click delete on last draft
     */
    public function iWaitAndClickDeleteOnLastDraft(): void
    {
        $locator = 'a.AknIconButton.AknIconButton--trash';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('No rules to remove found.');
        }

        $links = $this->getSession()->getPage()->findAll('css', $locator);
        $lastLink = end($links);
        $lastLink->click();
    }

    /**
     * @When I confirm delete
     */
    public function iConfirmDelete(): void
    {
        $cssLocator = 'div.AknButton.AknButtonList-item.AknButton--apply.ok.ok';
        if (!$this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $cssLocator)) {
            throw new \Exception('Delete button not found.');
        }
        $buttonDiv = $this->getSession()->getPage()->find('css', $cssLocator);
        $buttonDiv->click();
    }

    /**
     * @Then the number of rules should be lower by :quantity
     */
    public function theNumberOfResultsShouldBeLowerBy(int $quantity): void
    {
        $previous = $this->numberOfResults;
        if (!$previous) {
            throw new \Exception('Previous number of rules is 0.');
        }

        $this->waitToLoadPage('RULES');
        $newNumber = $this->getNumberOfResultsOnList();

        if ($previous - $quantity !== $newNumber) {
            throw new \Exception(
                'Wrong number of rules. Should be: ' . round($previous - $quantity) . ', is: ' . $newNumber
            );
        }
    }
}