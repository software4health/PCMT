<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Normalizer;

use PcmtRulesBundle\Entity\Rule;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Rule $object */
        return [
            'id'                => $object->getId(),
            'uniqueId'          => $object->getUniqueId(),
            'sourceFamily'      => $object->getSourceFamily()->getCode(),
            'destinationFamily' => $object->getDestinationFamily()->getCode(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Rule;
    }
}