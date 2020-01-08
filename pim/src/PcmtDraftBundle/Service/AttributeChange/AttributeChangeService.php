<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\AttributeChange;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtDraftBundle\Entity\AttributeChange;
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
        $value = '' === $value ? null : $value;
        $previousValue = '' === $previousValue ? null : $previousValue;

        if ($value === $previousValue) {
            return;
        }
        $attributeInstance = $this->attributeRepository->findOneByIdentifier($attribute);
        if (null !== $attributeInstance) {
            $attribute = $attributeInstance->getLabel();
        }
        $this->changes[] = new AttributeChange($attribute, (string) $previousValue, (string) $value);
    }

    public function getUniversal(?object $newObject, ?object $previousObject): array
    {
        $this->changes = [];

        if (!$newObject) {
            return $this->changes;
        }

        $newValues = $this->versioningSerializer->normalize($newObject, 'flat');
        $previousValues = $previousObject ?
            $this->versioningSerializer->normalize($previousObject, 'flat') :
            [];

        $attributes = array_unique(array_merge(array_keys($newValues), array_keys($previousValues)));
        foreach ($attributes as $attribute) {
            $newValue = $newValues[$attribute] ?? null;
            $previousValue = $previousValues[$attribute] ?? null;
            $attribute = (string) $attribute;
            $this->createChange($attribute, $newValue, $previousValue);
        }

        return $this->changes;
    }
}