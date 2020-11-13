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
}