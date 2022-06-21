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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ConnectorProductModelNormalizer
{
    /** @var ValuesNormalizer */
    private $valuesNormalizer;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var string */
    private $identifier;

    /** @var string */
    private $description;

    public function __construct(ValuesNormalizer $valuesNormalizer, EntityRepository $entityRepository, UrlGeneratorInterface $router, string $identifier, string $description)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->entityRepository = $entityRepository;
        $this->router = $router;
        $this->identifier = $identifier;
        $this->description = $description;
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
        $identifier = [];
        $description = [];
        $product_model_route = $this->router->generate(
            'pim_api_product_model_get',
            ['code' => $connectorProductModel->code()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        foreach ($attributes as $code) {
            $mapping = $repository->findOneByCode($code);
            if ($mapping) {
                $attribute_route = $this->router->generate(
                    'pim_api_attribute_get',
                    ['code' => $code],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                switch ($mapping->getMapping()) {
                    case $this->identifier:
                        $identifier[] = $this->mapIdentifier($attribute_route, $code, $product_model_route, $values);
                        break;
                    case $this->description:
                        $attributeTypeKey = '';
                        if ('pim_catalog_boolean' === $mapping->getType()) {
                            $attributeTypeKey = 'attributeTypeBoolean';
                        } elseif ('pim_catalog_number' === $mapping->getType()) {
                            $attributeTypeKey = 'attributeTypeInteger';
                        } else {
                            $attributeTypeKey = 'attributeTypeString';
                        }
                        $description[] = $this->mapDescription($attribute_route, $code, $values, $attributeTypeKey);
                        break;
                }
            }
        }

        return [
            'resourceType' => 'Product',
            'id'           => $connectorProductModel->code(),
            'identifier'   => $identifier,
            'description'  => $description,
        ];
    }

    private function mapIdentifier(string $attribute_route, string $code, string $product_model_route, array $values): array
    {
        return [
            'type' => [
                'coding' => [
                    'system'  => $attribute_route,
                    'code'    => $code,
                    'display' => $code,
                ],
                'text' => $code,
            ],
            'system' => $product_model_route,
            'value'  => $values[$code][0]['data'],
        ];
    }

    private function mapDescription(string $attribute_route, string $code, array $values, string $attributeTypeKey): array
    {
        return [
            'attributeType' => [
                'coding' => [
                    [
                        'system'  => $attribute_route,
                        'code'    => $code,
                        'display' => $code,
                    ],
                ],
                'text' => $code,
            ],
            $attributeTypeKey => $values[$code][0]['data'],
        ];
    }
}
