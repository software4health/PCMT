<?php

declare(strict_types=1);

namespace PcmtProductBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PcmtProductBundle\Entity\ExistingProductDraft;
use PcmtProductBundle\Entity\NewProductDraft;
use PcmtProductBundle\Entity\ProductDraftInterface;
use PcmtProductBundle\Service\AttributeChange\ProductAttributeChangeService;
use PcmtProductBundle\Service\Draft\ProductFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /** @var ProductFromDraftCreator */
    private $productFromDraftCreator;

    /** @var ProductAttributeChangeService */
    protected $productAttributeChangeService;

    /** @var NormalizerInterface */
    private $productNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        NormalizerInterface $productNormalizer
    ) {
        parent::__construct(
            $statusNormalizer,
            $attributeChangeNormalizer
        );

        $this->productNormalizer = $productNormalizer;
    }

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
        /** @var ProductDraftInterface $draft */
        $data = parent::normalize($draft, $format, $context);

        $newProduct = $this->productFromDraftCreator->getProductToCompare($draft);
        $data['label'] = $newProduct ? $this->getLabel($draft, $newProduct) : 'no label';

        $changes = $this->productAttributeChangeService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productNormalizer->normalize($newProduct, 'internal_api');
        }

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