<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer;

use PcmtCoreBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeChangeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($change, $format = null, array $context = []): array
    {
        $data = [];
        $data['attribute'] = $change->getAttributeName();
        $data['previousValue'] = $change->getPreviousValue();
        $data['newValue'] = $change->getNewValue();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AttributeChange;
    }
}