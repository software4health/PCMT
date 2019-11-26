<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Pcmt\PcmtProductBundle\Entity\DraftInterface;
use Pcmt\PcmtProductBundle\Entity\ExistingProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\ProductAttributeChangeService;
use Pcmt\PcmtProductBundle\Service\ProductFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /** @var ProductFromDraftCreator */
    private $productFromDraftCreator;

    /** @var ProductAttributeChangeService */
    protected $productAttributeChangeService;

    public function setProductFromDraftCreator(ProductFromDraftCreator $productFromDraftCreator): void
    {
        $this->productFromDraftCreator = $productFromDraftCreator;
    }

    public function setProductAttributeChangeService(ProductAttributeChangeService $productAttributeChangeService): void
    {
        $this->productAttributeChangeService = $productAttributeChangeService;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var ProductModelInterface $draft */
        $data = parent::normalize($draft, $format, $context);

        $productLabel = 'no label';
        $newProduct = $this->productFromDraftCreator->getProductToCompare($draft);
        /** @var DraftInterface $draft */
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

        $changes = $this->productAttributeChangeService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}