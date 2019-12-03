<?php

declare(strict_types=1);

namespace PcmtProductBundle\Normalizer;

use PcmtProductBundle\Entity\DraftInterface;
use PcmtProductBundle\Entity\DraftStatus;
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

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var DraftInterface $draft */
        $data = [];
        $data['id'] = $draft->getId();
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author ?
            $author->getFirstName() . ' ' . $author->getLastName() :
            'no author';
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);
        $data['type'] = ucfirst($draft->getType());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return false;
    }
}