<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\AttributeChange;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeChangeNormalizer implements NormalizerInterface
{
    /**
     * @param AttributeChange $change
     * @param null $format
     * @param array $context
     * @return array
     */
    public function normalize($change, $format = null, array $context = []): array
    {
        $data = [];
        $data['attribute'] = $change->getAttributeName();
        $data['previousValue'] = $change->getPreviousValue();
        $data['newValue'] = $change->getNewValue();
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AttributeChange;
    }
}