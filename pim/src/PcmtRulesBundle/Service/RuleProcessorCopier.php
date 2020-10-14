<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleProcessorCopier
{
    /** @var PropertyCopierInterface */
    private $propertyCopier;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ProductAttributeFilter */
    private $productAttributeFilter;

    /** @var ProductModelAttributeFilter */
    private $productModelAttributeFilter;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        PropertyCopierInterface $propertyCopier,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductAttributeFilter $productAttributeFilter,
        ProductModelAttributeFilter $productModelAttributeFilter,
        NormalizerInterface $normalizer
    ) {
        $this->propertyCopier = $propertyCopier;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->normalizer = $normalizer;
    }

    public function copy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        array $attributes
    ): bool {
        $productData = $this->normalizer->normalize($destinationProduct, 'standard');
        $attributeCodes = array_map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        }, $attributes);

        $productData['values'] = [];
        foreach ($attributeCodes as $code) {
            $productData['values'][$code] = 'value';
        }

        if ($destinationProduct instanceof ProductInterface) {
            $productData = $this->productAttributeFilter->filter($productData);
        } else {
            $productData = $this->productModelAttributeFilter->filter($productData);
        }

        if (0 === count($productData['values'])) {
            return false;
        }
        foreach ($attributes as $attribute) {
            if (isset($productData['values'][$attribute->getCode()])) {
                $this->copyOneAttribute($sourceProduct, $destinationProduct, $attribute);
            }
        }

        return true;
    }

    private function copyOneAttribute(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeInterface $attribute
    ): void {
        $scopes = $attribute->isScopable() ? $this->channelRepository->getChannelCodes() : null;
        $locales = $attribute->isLocalizable() ? $this->localeRepository->getActivatedLocaleCodes() : null;

        $scopes = $scopes ?? [null];
        $locales = $locales ?? [null];

        foreach ($locales as $localeCode) {
            foreach ($scopes as $scopeCode) {
                $options = [
                    'from_locale' => $localeCode,
                    'to_locale'   => $localeCode,
                    'from_scope'  => $scopeCode,
                    'to_scope'    => $scopeCode,
                ];
                $this->propertyCopier->copyData(
                    $sourceProduct,
                    $destinationProduct,
                    $attribute->getCode(),
                    $attribute->getCode(),
                    $options
                );
            }
        }
    }
}