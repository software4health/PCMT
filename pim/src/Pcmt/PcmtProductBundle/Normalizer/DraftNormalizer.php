<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\DraftInterface;
use Pcmt\PcmtProductBundle\Entity\DraftStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftNormalizer implements NormalizerInterface
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var AttributeChangeNormalizer */
    protected $attributeChangeNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
    }

    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var DraftInterface $draft */
        $data = [];
        $data['id'] = $draft->getId();
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author ?
            $author->getFirstName() . ' ' . $author->getLastName() : 'no author';
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return false;
    }
}