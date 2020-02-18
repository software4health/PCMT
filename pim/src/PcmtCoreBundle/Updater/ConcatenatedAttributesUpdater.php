<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Updater;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConcatenatedAttributesUpdater implements ObjectUpdaterInterface
{
    private const IS_MISSING = ' [missing]';
    private const IS_EMPTY = ' [empty]';

    /** @var ObjectUpdaterInterface */
    private $entityWithValuesUpdater;

    /** @var NormalizerInterface */
    private $rawValuesStorageFormatNormalizer;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    public function __construct(
        ObjectUpdaterInterface $entityWithValuesUpdater,
        NormalizerInterface $rawValuesStorageFormatNormalizer,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->entityWithValuesUpdater = $entityWithValuesUpdater;
        $this->rawValuesStorageFormatNormalizer = $rawValuesStorageFormatNormalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($object, array $data, array $options = []): void
    {
        $this->validateData($object, $data);

        $concatenatedAttribute = $data['concatenatedAttribute'];
        $concatenatedValue = [];
        foreach ($data['memberAttributes'] as $memberAttribute) {
            if (!$memberAttribute instanceof AttributeInterface) {
                throw new \InvalidArgumentException(
                    'Wrong type passed for update. ' . AttributeInterface::class . ' expected.'
                );
            }

            if ($object->hasAttribute($memberAttribute->getCode())) {
                $productValue = $object->getValue($memberAttribute->getCode());

                if (!(null === $productValue || '' === $productValue || [] === $productValue)) {
                    $concatenatedValue[] = $productValue->__toString();
                } else {
                    $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_EMPTY;
                }
            } else {
                $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_MISSING;
            }
        }

        if ($concatenatedAttribute->isScopable()) {
            $scopes = $this->channelRepository->getChannelCodes();
        }

        if ($concatenatedAttribute->isLocalizable()) {
            $locales = $this->localeRepository->getActivatedLocaleCodes();
        }

        $scopes = $scopes ?? [null];
        $locales = $locales ?? [null];

        foreach ($scopes as $scope) {
            foreach ($locales as $locale) {
                $this->updateWithSpecificScopeAndLocale($object, $concatenatedAttribute, $concatenatedValue, $scope, $locale);
            }
        }

        $this->computeRawValues($object);
    }

    private function computeRawValues(object $object): void
    {
        $values = $this->getValues($object);
        $rawValues = $this->rawValuesStorageFormatNormalizer->normalize($values, 'storage');
        $object->setRawValues($rawValues);
    }

    private function getValues(EntityWithValuesInterface $entity): WriteValueCollection
    {
        if ($entity instanceof ProductModelInterface) {
            return $entity->getValuesForVariation();
        }

        if ($entity instanceof ProductInterface) {
            return $entity->getValuesForVariation();
        }

        return $entity->getValues();
    }

    private function validateData(object $object, array $data): void
    {
        if (!array_key_exists('concatenatedAttribute', $data) ||
            (!$data['concatenatedAttribute'] instanceof AttributeInterface ||
                PcmtAtributeTypes::BACKEND_TYPE_CONCATENATED === !$data['concatenatedAttribute']->getType())) {
            throw new \InvalidArgumentException('Missing concatenated attribute to update.');
        }

        if (!array_key_exists('memberAttributes', $data) || !is_array($data['memberAttributes'])) {
            throw new \InvalidArgumentException('Missing member attributes key.');
        }

        if (!($object instanceof ProductInterface || $object instanceof ProductModelInterface)) {
            throw new \InvalidArgumentException('Improper data passed for concatenated attribute update.');
        }
    }

    private function updateWithSpecificScopeAndLocale(
        EntityWithValuesInterface $objectToUpdate,
        AttributeInterface $concatenatedAttribute,
        array $concatenatedValue,
        ?string $scope = null,
        ?string $locale = null
    ): void {
        $valuesToUpdate[$concatenatedAttribute->getCode()]['data'] = [
            'data' => array_reverse(
                [
                    implode(
                        $concatenatedAttribute->getProperty('separators'),
                        $concatenatedValue
                    ),
                ]
            ),
            'scope'  => $scope,
            'locale' => $locale,
        ];
        $this->entityWithValuesUpdater->update($objectToUpdate, $valuesToUpdate);
    }
}