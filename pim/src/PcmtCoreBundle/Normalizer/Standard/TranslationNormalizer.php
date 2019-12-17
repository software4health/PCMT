<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer as BaseTranslationNormalizer;

/**
 * Class TranslationNormalizer
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2018 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see                   http://www.dnd.fr/
 */
class TranslationNormalizer extends BaseTranslationNormalizer
{
    /**
     * @param mixed $object  Object to normalize
     * @param array $context Context options for the normalizer
     */
    public function normalizeDescription($object, array $context = []): array
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
}
