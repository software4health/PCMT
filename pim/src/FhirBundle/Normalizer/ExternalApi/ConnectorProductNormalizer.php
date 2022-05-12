<?php

declare(strict_types=1);

namespace FhirBundle\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Doctrine\ORM\EntityRepository;

/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
final class ConnectorProductNormalizer
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

    public function normalizeConnectorProductList(ConnectorProductList $connectorProducts): array
    {
        $normalizedProducts = [];
        foreach ($connectorProducts->connectorProducts() as $connectorProduct) {
            $normalizedProducts[] = $this->normalizeConnectorProduct($connectorProduct);
        }

        return $normalizedProducts;
    }

    public function normalizeConnectorProduct(ConnectorProduct $connectorProduct): array
    {
        $values = $this->valuesNormalizer->normalize($connectorProduct->values(), 'standard');
        $attr_values = $connectorProduct->attributeCodesInValues();
        $repository = $this->entityRepository;
        $identifier = '';
        $description = [
            'language'    => '',
            'description' => '',
        ];
        foreach ($attr_values as $code) {
            $mapping = $repository->findOneByCode($code);
            if ($mapping) {
                if ('identifier' === $mapping->getMapping()) {
                    $identifier = $values[$code][0]['data'];
                } elseif ('description' === $mapping->getMapping()) {
                    $description['language'] = $values[$code][0]['locale'];
                    $description['description'] = $values[$code][0]['data'];
                }
            }
        }

        return [
            'identifier'  => $identifier,
            'description' => [
                'language'    => $description['language'],
                'description' => $description['description'],
            ],
            'pim_identifier' => $connectorProduct->identifier(),
        ];
    }
}
