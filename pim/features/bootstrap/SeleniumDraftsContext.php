<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\DataFixtures\DraftFixtureFactory;

class SeleniumDraftsContext extends SeleniumBaseContext
{
    protected const DRAFT_STATUSES = [
        'New'      => AbstractDraft::STATUS_NEW,
        'Approved' => AbstractDraft::STATUS_APPROVED,
        'Rejected' => AbstractDraft::STATUS_REJECTED,
    ];

    /** @var array[int]  */
    protected $draftIds = [];

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
        $buttonDiv = $this->getSession()->getPage()->find('named_exact', ['id', 'Approve_pcmt_override']);
        $buttonDiv->click();
    }

    /**
     * @Then I should see my draft becoming the latest version of the product
     */
    public function iShouldSeeMyDraftBecomingTheLatestVersionOfTheProduct(): void
    {
        $this->waitForThePageToLoad(1000);
        foreach ($this->draftIds as $draftIdentifier) {
            $this->assertPageContainsText($draftIdentifier);
        }
    }
}