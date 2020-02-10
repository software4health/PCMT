<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\MassActions;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface;

class DraftsBulkApproveOperation implements BatchableOperationInterface
{
    /** @var string The background job code to launch */
    protected $jobInstanceCode;

    /** @var bool */
    protected $allSelected;

    /** @var int[] */
    protected $selected = [];

    /** @var int[] */
    protected $excluded = [];

    public function __construct(string $jobInstanceCode, bool $allSelected, array $selected, array $excluded)
    {
        $this->jobInstanceCode = $jobInstanceCode;
        $this->allSelected = $allSelected;
        $this->selected = $selected;
        $this->excluded = $excluded;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig(): array
    {
        return [
            'allSelected'     => $this->allSelected,
            'selected'        => $this->selected,
            'excluded'        => $this->excluded,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getJobInstanceCode(): string
    {
        return $this->jobInstanceCode;
    }
}