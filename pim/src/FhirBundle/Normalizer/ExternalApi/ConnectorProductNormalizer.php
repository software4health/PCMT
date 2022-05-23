<?php

declare(strict_types=1);

namespace FhirBundle\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(ValuesNormalizer $valuesNormalizer, EntityRepository $entityRepository, UrlGeneratorInterface $router)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->entityRepository = $entityRepository;
        $this->router = $router;
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
        $identifier = [];
        $description = [];
        $product_route = $this->router->generate(
            'pim_api_product_get',
            ['code' => $connectorProduct->identifier()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        foreach ($attr_values as $code) {
            $mapping = $repository->findOneByCode($code);
            if ($mapping) {
                if ('identifier' === $mapping->getMapping()) {
                    $attribute_route = $this->router->generate(
                        'pim_api_attribute_get',
                        ['code' => $code],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $identifier[] = [
                        'type' => [
                            'coding' => [
                                [
                                    'system'  => $attribute_route,
                                    'code'    => $code,
                                    'display' => $code,
                                ],
                            ],
                            'text' => $code,
                        ],
                        'system' => $product_route,
                        'value'  => $values[$code][0]['data'],
                    ];
                } elseif ('description' === $mapping->getMapping()) {
                    $description['language'] = $values[$code][0]['locale'];
                    $description['description'] = $values[$code][0]['data'];
                }
            }
        }

        return [
            'resourceType' => 'Item',
            'id'           => $connectorProduct->identifier(),
            'identifier'   => $identifier,
            'description'  => $description,
        ];
    }
}
