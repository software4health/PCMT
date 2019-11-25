<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\ProductModelDraftInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class DraftNormalizer
 */
class ProductModelDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /**
     * @param ProductModelDraftInterface $draft
     * @param null                       $format
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        $data = parent::normalize($draft, $format, $context);

        $data['label'] = 'Draft of product model';
        $data['changes'] = []; // @todo implement for product model
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductModelDraftInterface;
    }
}