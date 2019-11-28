<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Pcmt\PcmtProductBundle\Entity\ExistingProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\AttributeChange\ProductAttributeChangeService;
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

        $newProduct = $this->productFromDraftCreator->getProductToCompare($draft);
        $data['label'] = $this->getLabel($draft, $newProduct);

        $changes = $this->productAttributeChangeService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        return $data;
    }

    private function getLabel(ProductDraftInterface $draft, ProductInterface $newProduct): string
    {
        switch (get_class($draft)) {
            case NewProductDraft::class:
                return $newProduct->getIdentifier();
            case ExistingProductDraft::class:
                return $draft->getProduct()->getIdentifier();
        }

        return '--';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}