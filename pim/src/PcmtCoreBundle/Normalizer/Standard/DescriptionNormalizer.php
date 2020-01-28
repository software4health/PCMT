<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DescriptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $context = array_merge(
            [
                'property' => 'description',
                'locales'  => [],
            ],
            $context
        );

        $translations = array_fill_keys($context['locales'], null);
        $method = sprintf('get%s', ucfirst($context['property']));

        foreach ($object->getTranslations() as $translation) {
            if (false === method_exists($translation, $method)) {
                throw new \LogicException(
                    sprintf("Class %s doesn't provide method %s", get_class($translation), $method)
                );
            }

            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $translations[$translation->getLocale()] = '' === $translation->{$method}() ? null : $translation->{$method}();
            }
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeInterface;
    }
}
