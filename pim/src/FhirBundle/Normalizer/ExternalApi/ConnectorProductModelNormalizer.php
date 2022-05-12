<?php
/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace FhirBundle\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Doctrine\ORM\EntityRepository;

final class ConnectorProductModelNormalizer
{
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(ValuesNormalizer $valuesNormalizer, EntityRepository $entityRepository)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->entityRepository = $entityRepository;
    }

    public function normalizeConnectorProductModelList(ConnectorProductModelList $list): array
    {
        return array_map(function (ConnectorProductModel $connectorProductModel): array {
            return $this->normalizeConnectorProductModel($connectorProductModel);
        }, $list->connectorProductModels());
    }

    public function normalizeConnectorProductModel(ConnectorProductModel $connectorProductModel): array
    {
        $values = $this->valuesNormalizer->normalize($connectorProductModel->values(), 'standard');
        $attributes = $connectorProductModel->attributeCodesInValues();
        $repository = $this->entityRepository;
        $identifier = '';
        $description = ['attributeType' => ''];
        foreach ($attributes as $code) {
            $mapping = $repository->findOneByCode($code);
            if ($mapping) {
                if ('identifier' === $mapping->getMapping()) {
                    $identifier = $values[$code][0]['data'];
                } elseif ('description' === $mapping->getMapping()) {
                    if ('pim_catalog_boolean' === $mapping->getType()) {
                        $description['attributeTypeBoolean'] = $values[$code][0]['data'];
                    } elseif ('pim_catalog_number' === $mapping->getType()) {
                        $description['attributeTypeInteger'] = $values[$code][0]['data'];
                    } else {
                        $description['attributeTypeString'] = $values[$code][0]['data'];
                    }
                }
            }
        }

        return [
            'identifier'  => $identifier,
            'description' => $description,
            'code'        => $connectorProductModel->code(),
        ];
    }
}
