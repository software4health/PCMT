<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\ExistingProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\ProductFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DraftNormalizer
 */
class ProductDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /** @var ProductFromDraftCreator */
    private $productFromDraftCreator;

    public function setProductFromDraftCreator(ProductFromDraftCreator $productFromDraftCreator): void
    {
        $this->productFromDraftCreator = $productFromDraftCreator;
    }

    /**
     * @param ProductDraftInterface $draft
     * @param null                  $format
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        $data = parent::normalize($draft, $format, $context);

        $productLabel = 'no label';
        $newProduct = $this->productFromDraftCreator->getProductToCompare($draft);
        /** @var ProductDraftInterface $draft */
        switch (get_class($draft)) {
            case NewProductDraft::class:
                $productLabel = $newProduct->getIdentifier();
                break;
            case ExistingProductDraft::class:
                $product = $draft->getProduct();
                $productLabel = $product ? $product->getIdentifier() : 'no product id';
                break;
        }
        $data['label'] = $productLabel;
        $changes = $this->attributeChangesService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}