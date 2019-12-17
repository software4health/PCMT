<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Reader;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;

interface CrossJoinExportReaderInterface extends ItemReaderInterface
{
    /**
     * @return mixed|null
     *
     * @throws ObjectNotFoundException
     */
    public function readCross();

    public function setFamilyToCrossRead(string $familyToCrossRead): void;
}