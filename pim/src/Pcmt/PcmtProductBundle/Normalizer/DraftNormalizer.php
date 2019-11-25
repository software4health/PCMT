<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\DraftInterface;
use Pcmt\PcmtProductBundle\Entity\DraftStatus;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\AttributeChangesService;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class DraftNormalizer
 */
class DraftNormalizer implements NormalizerInterface
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var AttributeChangeNormalizer */
    protected $attributeChangeNormalizer;

    /** @var AttributeChangesService */
    protected $attributeChangesService;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        AttributeChangesService $attributeChangesService
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
        $this->attributeChangesService = $attributeChangesService;
    }

    /**
     * @param DraftInterface $draft
     * @param null           $format
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
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
        return $data instanceof ProductDraftInterface;
    }
}