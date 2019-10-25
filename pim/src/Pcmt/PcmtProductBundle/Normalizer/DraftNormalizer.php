<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DraftNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface $draftPropertyNormalizer */
    protected $draftPropertyNormalizers;
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    public function __construct(array $draftPropertyNormalizers)
    {
        $this->draftPropertyNormalizers = $draftPropertyNormalizers;
    }

    public function normalize($draft, $format = null, array $context = []): array
    {
        $draftHistory = $draft->getDraftHistoryEntries();
        $normalizedDraftHistory = [];

        foreach ($draftHistory as $entry){
            $normalizedDraftHistory[] = [
                'changeset' => $entry->getChangeSet(),
                'date' => $entry->getCreatedAt()
            ];
        }
        $normalizedDraft = [
            'id' => $draft->getId(),
            'author' => $draft->getAuthor()->getUserName(),
            'product' => (null != $draft->getProduct()) ? $draft->getProduct()->getName() : $draft->getProductData(),
            'type' => $draft->getType(),
            'history' => $normalizedDraftHistory
        ];

        return $normalizedDraft;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductDraftInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}