<?php

declare(strict_types=1);

namespace PcmtProductBundle\Normalizer;

use PcmtProductBundle\Entity\ExistingProductDraft;
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