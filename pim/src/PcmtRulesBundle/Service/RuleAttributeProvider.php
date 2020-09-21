<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class RuleAttributeProvider
{
    public const TYPE_IDENTIFIER = 'pim_catalog_identifier';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    private function filterForType(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (self::TYPE_IDENTIFIER === $attribute->getType()) {
                return false;
            }

            return true;
        }));
    }

    public function getForFamilies(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes1 = $this->filterForType($attributes1);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes2 = $this->filterForType($attributes2);

        return array_intersect($attributes1, $attributes2);
    }
}