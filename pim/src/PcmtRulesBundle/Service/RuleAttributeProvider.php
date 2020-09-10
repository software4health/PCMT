<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class RuleAttributeProvider
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function getForFamilies(Family $sourceFamily, Family $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);

        return array_intersect($attributes1, $attributes2);
    }
}