<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter;
use PcmtCoreBundle\Connector\Job\Writer\File\E2OpenFlatItemBufferFlusher;

class E2OpenWriter extends ProductWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'identifier' => $item['identifier'],
                'values'     => $item['values'],
            ];
        }

        parent::write($result);
    }

    public function setFlatItemBufferFlusher(E2OpenFlatItemBufferFlusher $flatItemBufferFlusher): void
    {
        $this->flusher = $flatItemBufferFlusher;
    }
}
