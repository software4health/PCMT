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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PcmtRulesBundle\Event\ProductChangedEvent;
use PcmtRulesBundle\Event\ProductModelChangedEvent;
use PcmtRulesBundle\Value\AttributeMapping;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        PropertyCopierInterface $propertyCopier,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductAttributeFilter $productAttributeFilter,
        ProductModelAttributeFilter $productModelAttributeFilter,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertyCopier = $propertyCopier;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->normalizer = $normalizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function copy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        array $mappings
    ): bool {
        $productData = $this->normalizer->normalize($destinationProduct, 'standard');
        $attributeCodes = array_map(function (AttributeMapping $attributeMapping) {
            return $attributeMapping->getDestinationAttribute()->getCode();
        }, $mappings);

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
        foreach ($mappings as $mapping) {
            if (isset($productData['values'][$mapping->getDestinationAttribute()->getCode()])) {
                $this->copyOneAttribute($sourceProduct, $destinationProduct, $mapping->getSourceAttribute(), $mapping->getDestinationAttribute());
            }
        }

        return true;
    }

    private function copyOneAttribute(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeInterface $sourceAttribute,
        AttributeInterface $destinationAttribute
    ): void {
        $scopes = $sourceAttribute->isScopable() ? $this->channelRepository->getChannelCodes() : null;
        $locales = $sourceAttribute->isLocalizable() ? $this->localeRepository->getActivatedLocaleCodes() : null;

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
                $previousValue = $destinationProduct->getValue($destinationAttribute->getCode(), $localeCode, $scopeCode);
                $newValue = $sourceProduct->getValue($sourceAttribute->getCode(), $localeCode, $scopeCode);

                if ((!$previousValue && $newValue) || ($previousValue && !$previousValue->isEqual($newValue))) {
                    $this->propertyCopier->copyData(
                        $sourceProduct,
                        $destinationProduct,
                        $sourceAttribute->getCode(),
                        $destinationAttribute->getCode(),
                        $options
                    );

                    if ($destinationProduct instanceof ProductModelInterface) {
                        $event = new ProductModelChangedEvent(
                            $destinationProduct,
                            $destinationAttribute,
                            $localeCode,
                            $scopeCode,
                            $previousValue,
                            $newValue
                        );
                        $this->eventDispatcher->dispatch(ProductModelChangedEvent::NAME, $event);
                    } else {
                        $event = new ProductChangedEvent(
                            $destinationProduct,
                            $destinationAttribute,
                            $localeCode,
                            $scopeCode,
                            $previousValue,
                            $newValue
                        );
                        $this->eventDispatcher->dispatch(ProductChangedEvent::NAME, $event);
                    }
                }
            }
        }
    }
}