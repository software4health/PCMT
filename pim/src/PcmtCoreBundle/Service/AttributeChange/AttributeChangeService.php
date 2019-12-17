<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\AttributeChange;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtCoreBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeChangeService
{
    /** @var AttributeChange[] */
    protected $changes = [];

    /** @var SerializerInterface */
    protected $versioningSerializer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    public function __construct(SerializerInterface $versioningSerializer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->versioningSerializer = $versioningSerializer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array|object|string|int|null $value
     * @param array|object|string|int|null $previousValue
     */
    protected function createChange(string $attribute, $value, $previousValue): void
    {
        $value = $value ?: null;
        $previousValue = $previousValue ?: null;

        if ($value === $previousValue) {
            return;
        }
        $attributeInstance = $this->attributeRepository->findOneByIdentifier($attribute);
        if (null !== $attributeInstance) {
            $attribute = $attributeInstance->getLabel();
        }
        $this->changes[] = new AttributeChange($attribute, (string) $previousValue, (string) $value);
    }
}