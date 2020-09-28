<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class RuleAttributeProvider
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    private function getSupportedTypes(): array
    {
        return [
            AttributeTypes::TEXT,
            AttributeTypes::OPTION_SIMPLE_SELECT,
        ];
    }

    private function filterForType(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (in_array($attribute->getType(), $this->getSupportedTypes())) {
                return true;
            }

            return false;
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