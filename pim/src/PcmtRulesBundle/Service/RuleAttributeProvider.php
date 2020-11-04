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
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
        ];
    }

    private function filterForKeyAttribute(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (!in_array($attribute->getType(), $this->getSupportedTypes())) {
                return false;
            }

            if ($attribute->isLocalizable() || $attribute->isScopable()) {
                return false;
            }

            return true;
        }));
    }

    private function filterOutIdentifiers(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (AttributeTypes::IDENTIFIER !== $attribute->getType()) {
                return true;
            }

            return false;
        }));
    }

    public function getAllForFamilies(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes1 = $this->filterOutIdentifiers($attributes1);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes2 = $this->filterOutIdentifiers($attributes2);

        return array_intersect($attributes1, $attributes2);
    }

    public function getPossibleForKeyAttribute(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes1 = $this->filterForKeyAttribute($attributes1);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes2 = $this->filterForKeyAttribute($attributes2);

        return array_intersect($attributes1, $attributes2);
    }
}