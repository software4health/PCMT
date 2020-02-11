<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\E2Open;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use Psr\Log\LoggerInterface;

class TradeItemXmlProcessor
{
    /** @var LoggerInterface */
    private $logger;

    /** @var E2OpenAttributesService */
    private $attributesService;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ProductInterface */
    private $productToUpdate;

    public function __construct(
        LoggerInterface $logger,
        E2OpenAttributesService $attributesService,
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->logger = $logger;
        $this->attributesService = $attributesService;
        $this->productUpdater = $productUpdater;
    }

    public function setProductToUpdate(ProductInterface $product): void
    {
        $this->productToUpdate = $product;
    }

    public function processNode(array $element, string $parent = ''): void
    {
        if (!empty($element['attributes'])) {
            // there are some additional attributes in node, process them individually
            foreach ($element['attributes'] as $name => $value) {
                $newElement = [
                    'name'  => $name,
                    'value' => $value,
                ];
                $this->processNode($newElement, $element['name']);
            }
            // but don't finish processing here, process also whole node.
        }

        if (is_array($element['value'])) {
            // there are still further nodes
            foreach ($element['value'] as $subElement) {
                $this->processNode($subElement, $element['name']);
            }
            // finish processing node in such case
            return;
        }

        $name = $parent . $element['name'];
        if (!$mappedAttributeCode = E2OpenMapping::findMappingForKey($name)) {
            $name = $element['name'];
            $mappedAttributeCode = E2OpenMapping::findMappingForKey($name);
        }

        if (!$mappedAttributeCode) {
            // no mapping defined for this node
            return;
        }
        try {
            $value = E2OpenMapping::mapValue($element['value']);
            $this->processProductAttributeValue($mappedAttributeCode, $value, $element['attributes'] ?? []);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Processing key ' . $element['name'] . ' failed. Key and value will be ignored. ' .
                'Details: ' . $exception->getMessage()
            );
        }
    }

    /**
     * @param string|int $value
     *
     * @throws \Exception
     */
    private function processProductAttributeValue(string $mappedAttributeCode, $value, ?array $nodeAttributes): void
    {
        $pcmtAttribute = $this->attributesService->getForCode($mappedAttributeCode);

        if (!$pcmtAttribute) {
            throw new \Exception('Attribute not found for ' . $mappedAttributeCode);
        }

        $unit = null;
        if (E2OpenAttributesService::MEASURE_UNIT === $pcmtAttribute->getMetricFamily()) {
            foreach ($nodeAttributes as $name => $v) {
                if (false !== mb_stripos($name, 'measurementUnitCode')) {
                    if ($u = $this->attributesService->getMeasureUnitForSymbol($v)) {
                        $unit = $u;
                    }
                }
            }
        }

        if ($unit) {
            // if measurement unit found, this is a special metric field and we need to send array instead of string/int
            $value = [
                'unit'   => $unit,
                'amount' => $value,
            ];
        }

        $valuesToUpdate[$pcmtAttribute->getCode()]['data']['data'] = $value;
        $valuesToUpdate[$pcmtAttribute->getCode()]['data']['locale'] = null;
        $valuesToUpdate[$pcmtAttribute->getCode()]['data']['scope'] = null;

        $this->productUpdater->update(
            $this->productToUpdate,
            [
                'values' => $valuesToUpdate,
            ]
        );
    }
}
