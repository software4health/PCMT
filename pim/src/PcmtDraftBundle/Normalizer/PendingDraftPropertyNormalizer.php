<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use PcmtDraftBundle\Entity\ExistingProductDraft;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PendingDraftPropertyNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): void
    {
        throw new MethodNotImplementedException('method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ExistingProductDraft;
    }
}