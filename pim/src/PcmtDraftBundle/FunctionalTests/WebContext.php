<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\FunctionalTests;

use Behat\Mink\Exception\ExpectationException;
use PcmtDraftBundle\DataFixtures\DraftFixtureFactory;
use PcmtDraftBundle\Entity\AbstractDraft;

class WebContext extends \SeleniumBaseContext
{
    protected const DRAFT_STATUSES = [
        'New'      => AbstractDraft::STATUS_NEW,
        'Approved' => AbstractDraft::STATUS_APPROVED,
        'Rejected' => AbstractDraft::STATUS_REJECTED,
    ];

    /** @var int[] */
    protected $draftIds = [];

    /**
     * @When I click approve on last draft
     */
    public function iClickApproveOnLastDraft(): void
    {
        $links = $this->getSession()->getPage()->findAll(
            'css',
            'a.AknIconButton.draft-approve'
        );
        $lastLink = end($links);
        $lastLink->click();
    }

    /**
     * @Given There is :num quantity of drafts with status :status
     */
    public function thereIsQuantityOfDraftsWithStatus(int $num, string $status): void
    {
        $em = $this->getEntityManager();
        $draftFixture = (new DraftFixtureFactory())
            ->createDraft(self::DRAFT_STATUSES[$status]);

        for ($i = 0; $i < $num; $i++) {
            $draftFixture->load($em);
            $this->draftIds[] = $draftFixture->getDraftIdentifier();
        }
    }

    /**
     * @When I confirm approval
     */
    public function iConfirmApproval(): void
    {
        $this->getSession()->wait(2000);

        $buttonDiv = $this->getSession()->getPage()->find('css', 'div.AknButton.AknButtonList-item.AknButton--apply.ok.ok');
        $buttonDiv->click();
    }

    /**
     * @When I select :num draft checkboxes for mass action
     */
    public function iSelectDraftCheckboxesForMassAction(int $num): void
    {
        $checkboxes = $this->getSession()->getPage()->findAll(
            'css',
            'td.AknGrid-bodyCell.input-cell.draft-checkbox-bodyCell.AknGrid-bodyCell--actions > label > input'
        );

        //always select last added drafts
        for ($i = count($checkboxes) - 1; $i >= (count($checkboxes) - $num); $i--) {
            $checkbox = $checkboxes[$i];
            if ($checkbox instanceof \Behat\Mink\Element\NodeElement) {
                $checkbox->check();
            }
        }

        $this->getSession()->wait(1000);
    }

    /**
     * @Then I should see my draft becoming the latest version of the product
     */
    public function iShouldSeeMyDraftBecomingTheLatestVersionOfTheProduct(): void
    {
        foreach ($this->draftIds as $draftIdentifier) {
            $this->assertPageContainsText($draftIdentifier);
        }
    }

    /**
     * @Then I should see my draft becoming the latest version of the product, try :attempts times
     */
    public function iShouldSeeMyDraftBecomingTheLatestVersionOfTheProductManyTryTimes(int $attempts): void
    {
        // we make a number of attempts, as the job in background may last for some time
        for ($i = 1; $i < $attempts; $i++) {
            try {
                $this->iShouldSeeMyDraftBecomingTheLatestVersionOfTheProduct();

                return;
            } catch (ExpectationException $e) {
                $this->getSession()->reload();
                $this->getSession()->wait(2000);
            }
        }
        $this->iShouldSeeMyDraftBecomingTheLatestVersionOfTheProduct();
    }
}