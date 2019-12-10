<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PcmtCoreBundle\Entity\ExistingProductModelDraft;
use PcmtCoreBundle\Entity\NewProductModelDraft;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;
use PcmtCoreBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtCoreBundle\Service\Draft\ProductModelFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductModelDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /** @var ProductModelFromDraftCreator */
    private $productModelFromDraftCreator;

    /** @var ProductModelAttributeChangeService */
    protected $productModelAttributeChangeService;

    /** @var NormalizerInterface */
    protected $productModelNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        NormalizerInterface $productModelNormalizer
    ) {
        parent::__construct($statusNormalizer, $attributeChangeNormalizer);

        $this->productModelNormalizer = $productModelNormalizer;
    }

    public function setProductModelFromDraftCreator(ProductModelFromDraftCreator $productModelFromDraftCreator): void
    {
        $this->productModelFromDraftCreator = $productModelFromDraftCreator;
    }

    public function setProductModelAttributeChangeService(ProductModelAttributeChangeService $productModelAttributeChangeService): void
    {
        $this->productModelAttributeChangeService = $productModelAttributeChangeService;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var ProductModelDraftInterface $draft */
        $data = parent::normalize($draft, $format, $context);

        $newProductModel = $this->productModelFromDraftCreator->getProductModelToCompare($draft);
        $data['label'] = $this->getLabel($draft, $newProductModel);

        $changes = $this->productModelAttributeChangeService->get($newProductModel, $draft->getProductModel());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productModelNormalizer->normalize($newProductModel, 'internal_api');
        }

        return $data;
    }

    private function getLabel(ProductModelDraftInterface $draft, ProductModelInterface $newProductModel): string
    {
        switch (get_class($draft)) {
            case NewProductModelDraft::class:
                return $newProductModel->getCode();
            case ExistingProductModelDraft::class:
                return $draft->getProductModel()->getCode();
        }

        return '--';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductModelDraftInterface;
    }
}