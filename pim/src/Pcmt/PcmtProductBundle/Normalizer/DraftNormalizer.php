<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\DraftStatus;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\AttributeChangesService;
use Pcmt\PcmtProductBundle\Service\ProductFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class DraftNormalizer implements NormalizerInterface
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var ProductFromDraftCreator */
    private $productFromDraftCreator;

    /** @var AttributeChangesService */
    private $attributeChangesService;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        ProductFromDraftCreator $productFromDraftCreator,
        AttributeChangesService $attributeChangesService
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
        $this->productFromDraftCreator = $productFromDraftCreator;
        $this->attributeChangesService = $attributeChangesService;
    }

    /**
     * @param ProductDraftInterface $draft
     * @param null                  $format
     * @param array                 $context
     *
     * @return array
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        $data = [];
        $data['id'] = $draft->getId();
        $productLabel = 'no label';
        $newProduct = $this->productFromDraftCreator->getProductToCompare($draft);
        /** @var ProductDraftInterface $draft */
        switch (get_class($draft)) {
            case NewProductDraft::class:
                $productLabel = $newProduct->getIdentifier();

                break;
            case PendingProductDraft::class:
                $product = $draft->getProduct();
                $productLabel = $product ? $product->getIdentifier() : 'no product id';

                break;
        }
        $data['label'] = $productLabel;
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author ?
            $author->getFirstName() . ' ' . $author->getLastName() : 'no author';

        $changes = $this->attributeChangesService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}