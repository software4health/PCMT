<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PcmtRulesBundle\Entity\Rule;

class RuleProductProcessor
{
    /** @var ProductQueryBuilderFactory */
    private $pqbFactory;

    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var PropertyCopierInterface */
    private $propertyCopier;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        ProductQueryBuilderFactory $pqbFactory,
        RuleAttributeProvider $ruleAttributeProvider,
        PropertyCopierInterface $propertyCopier,
        SaverInterface $productSaver
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->propertyCopier = $propertyCopier;
        $this->productSaver = $productSaver;
    }

    public function process(Rule $rule, ProductInterface $sourceProduct): int
    {
        $attributes = $this->ruleAttributeProvider->getForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $keyValue = $sourceProduct->getValue($rule->getKeyAttribute()->getCode());
        if (!$keyValue) {
            return 0;
        }
        // searching through ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
        ]);
        $pqb->addFilter($rule->getKeyAttribute()->getCode(), Operators::IN_LIST, [$keyValue->getData()]);
        $pqb->addFilter('family', Operators::IN_LIST, [$rule->getDestinationFamily()->getCode()]);

        $destinationProducts = $pqb->execute();
        $i = 0;
        foreach ($destinationProducts as $destinationProduct) {
            foreach ($attributes as $attribute) {
                if ('pim_catalog_identifier' !== $attribute->getType()) {
                    $this->propertyCopier->copyData(
                        $sourceProduct,
                        $destinationProduct,
                        $attribute->getCode(),
                        $attribute->getCode()
                    );
                }
            }
            $this->productSaver->save($destinationProduct);
            $i++;
        }

        return $i;
    }
}