<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Reader;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class MstSupplierExportReader extends ProductReader implements CrossJoinExportReaderInterface
{
    /** @var CursorInterface */
    protected $crossProducts;

    /** @var bool */
    private $firstCrossRead = true;

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->firstCrossRead = true;
    }

    public function setFamilyToCrossRead(string $familyToCrossRead): void
    {
        $filters = [
            [
                'field'    => 'family',
                'value'    => [
                    0 => $familyToCrossRead,
                ],
                'operator' => 'IN',
            ],
        ];
        $this->crossProducts = $this->getProductsCursor($filters, $this->getConfiguredChannel());
    }

    /**
     * {@inheritdoc}
     */
    public function readCross()
    {
        $product = null;

        if ($this->crossProducts->valid()) {
            if (!$this->firstCrossRead) {
                $this->crossProducts->next();
            }
            $product = $this->crossProducts->current();
        }

        if (null !== $product) {
            $this->stepExecution->incrementSummaryInfo('read_cross');

            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($product, $channel);
            }
        }

        $this->firstCrossRead = false;

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguredFilters()
    {
        $filters = parent::getConfiguredFilters();

        foreach ($filters as $key => $filter) {
            if ('family' === $filter['field']) {
                unset($filters[$key]);
                break;
            }
        }
        $filters[] = [
            'field'    => 'family',
            'value'    => [
                0 => 'MD_HUB',
            ],
            'operator' => 'IN',
        ];

        return $filters;
    }
}
