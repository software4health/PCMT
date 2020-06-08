<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\FunctionalTests;

use Behat\Behat\Context\Context;
use WebContentFinder;

class WebContext extends \SeleniumBaseContext implements Context
{
    /** @var string[] */
    private $chosenPermissions = [];
    /**
     * @When I go to :nth category tree child
     * clicks on the nth child in category tree view
     */
    public function goToCategoryTreeChild(int $nth = 1): void
    {
        $locator = '#node_1 > ul > li > a';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('Link not found.');
        }
        $tree = $this->getSession()->getPage()->findAll('css', $locator);

        if (!$tree[$nth]) {
            throw new \InvalidArgumentException('Link does not exist');
        }

        $tree[$nth]->click();
    }

    /**
     * @And I clear default permissions
     * @When I clear default permissions
     */
    public function iClearDefaultPermissions(): void
    {
        $this->clearAllPermissionInputs();
    }

    /**
     * @When I edit permissions with parameters :viewAccess :editAccess :ownProducts
     * @And I edit permissions with parameters :viewAccess :editAccess :ownProducts
     */
    public function editPermissionsWithParameters(
        string $viewAccessGroup,
        string $editAccessGroup,
        string $ownAccessGroup
    ): void {
        $accessGroups = [
            '#s2id_pim_category_viewAccess > ul' => $viewAccessGroup,
            '#s2id_pim_category_editAccess > ul' => $editAccessGroup,
            '#s2id_pim_category_ownAccess > ul'  => $ownAccessGroup,
        ];
        //s2id_pim_category_viewAccess > ul
        $this->clearAllPermissionInputs();
        array_walk(
            $accessGroups,
            function ($group, $select): void {
                $this->clickOnSelect($select);
                $this->selectAccessOption($group);
            }
        );

        $this->chosenPermissions = [$viewAccessGroup, $editAccessGroup, $ownAccessGroup];
    }

    /**
     * @And I save
     * @When I save
     */
    public function iSave(): void
    {
        $locator = 'div.AknTitleContainer-actionsContainer.AknButtonList > button';
        $this->waitUntil(\WebContentFinder::SAVE_BUTTON_EXISTS, $locator);
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @Then I should see correct permissions set
     */
    public function iShouldSeeCorrectPermissionsSet(): void
    {
        $selectors = $this->getAllPermissionInputValues();
        array_walk($selectors, function ($selector): void {
            if (!in_array($selector->getText(), $this->chosenPermissions)) {
                throw new \Exception('Permissions are not correctly set.');
            }
        });
    }

    private function clickOnSelect(string $selectLocator): void
    {
        $selector = $this->getSession()->getPage()->find('css', $selectLocator);
        $selector->click();
    }

    private function selectAccessOption(string $accessGroup): void
    {
        $locators = '#select2-drop > ul > li';
        $selectors = $this->getSession()->getPage()->findAll('css', $locators);
        foreach ($selectors as $selector) {
            if ($selector->getText() === $accessGroup) {
                $selector->click();

                return;
            }
        }
    }

    private function getAllPermissionInputValues(): array
    {
        $locators = [
            '#s2id_pim_category_viewAccess > ul > li.select2-search-choice > div',
            '#s2id_pim_category_editAccess > ul > li.select2-search-choice > div',
            '#s2id_pim_category_ownAccess > ul > li.select2-search-choice > div',
        ];

        return array_map(
            function ($locator) {
                return $this->getSession()->getPage()->find('css', $locator);
            },
            $locators
        );
    }

    private function getAllPermissionInputs(): array
    {
        $locators = [
            '#s2id_pim_category_viewAccess > ul > li.select2-search-choice > a',
            '#s2id_pim_category_editAccess > ul > li.select2-search-choice > a',
            '#s2id_pim_category_ownAccess > ul > li.select2-search-choice > a',
        ];

        return array_map(
            function ($locator) {
                return $this->getSession()->getPage()->find('css', $locator);
            },
            $locators
        );
    }

    private function clearAllPermissionInputs(): void
    {
        $inputs = $this->getAllPermissionInputs();
        array_walk(
            $inputs,
            function ($input): void {
                $input->click();
            }
        );
    }
}