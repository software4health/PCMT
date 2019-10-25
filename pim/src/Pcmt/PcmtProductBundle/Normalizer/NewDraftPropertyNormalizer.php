<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NewDraftPropertyNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        throw new MethodNotImplementedException('method not implemented');
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof NewProductDraft;
    }
}