<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 *
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\Handler;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Entity\Mapping\AttributeMapping;
use PcmtCoreBundle\Repository\AttributeMappingRepository;
use PcmtCoreBundle\Entity\Attribute;

class E2OpenMappingHandler
{
    private const MAPPING_TYPE = 'E2Open';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $attributeList;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeMappingRepository */
    private $attributeMappingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AttributeMappingRepository $attributeMappingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->attributeList = E2OpenMapping::getE2OpenAttributeNames();
    }

    public function createMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {
        $mapping = $this->findMapping($mappingAttribute->getCode(), $mappedAttribute->getCode()) ?? AttributeMapping::create(
                self::MAPPING_TYPE
            );

    }

    private function findMapping(string $mappingAttribute, string $mappedAttribute): ?AttributeMapping
    {
        return $this->attributeMappingRepository->findBy(
            [
                'name' => $this->composeName($mappingAttribute, $mappedAttribute),
                'type' => self::MAPPING_TYPE
            ]
        );
    }

    private function composeName(string $attribute1, string $attribute2)
    {
        return $attribute1.'_'.$attribute2;
    }
}


