<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\Handler;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Entity\Mapping\AttributeMapping;
use PcmtCoreBundle\Exception\Mapping\AttributeNotInFamilyException;
use PcmtCoreBundle\Repository\AttributeMappingRepository;

class E2OpenMappingHandler implements AttributeMappingHandlerInterface
{
    private const MAPPING_TYPE = 'E2Open';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Attribute[] */
    private $attributeList = [];

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeMappingRepository */
    private $attributeMappingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        FamilyRepositoryInterface $familyRepository,
        AttributeMappingRepository $attributeMappingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->familyRepository = $familyRepository;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->attributeList = E2OpenMapping::getE2OpenAttributeNames();
    }

    public function createMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {
        if (!$this->validateAttributeIsE2Open($mappingAttribute)) {
            throw new AttributeNotInFamilyException(
                'Attribute ' . $mappingAttribute->getCode() . ' does not belong to family.'
            );
        }
        $mapping = $this->findMapping(
            $mappingAttribute,
            $mappedAttribute
        ) ?? AttributeMapping::create(
            self::MAPPING_TYPE,
            $mappingAttribute,
            $mappedAttribute
        );
        $this->entityManager->persist($mapping);
        $this->entityManager->flush();
    }

    public function getAttributeList(): array
    {
        return $this->attributeList;
    }

    private function findMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): ?AttributeMapping
    {
        return $this->attributeMappingRepository->findOneBy(
            [
                'name'        => $this->composeName($mappingAttribute, $mappedAttribute),
                'mappingType' => self::MAPPING_TYPE,
            ]
        );
    }

    private function validateAttributeIsE2Open(Attribute $attribute): bool
    {
        $family = $this->familyRepository->findOneBy(
            [
                'code' => 'GS1_GDSN',
            ]
        );

        return $family->hasAttribute($attribute);
    }

    private function composeName(Attribute $attribute1, Attribute $attribute2): string
    {
        return AttributeMapping::composeName($attribute1, $attribute2);
    }
}