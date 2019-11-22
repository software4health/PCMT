<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PendingDraftPropertyNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        throw new MethodNotImplementedException('method not implemented');
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PendingProductDraft;
    }
}