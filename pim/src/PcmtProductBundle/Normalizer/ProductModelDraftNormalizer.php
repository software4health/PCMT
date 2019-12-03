<?php

declare(strict_types=1);

namespace PcmtProductBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PcmtProductBundle\Entity\ExistingProductModelDraft;
use PcmtProductBundle\Entity\NewProductModelDraft;
use PcmtProductBundle\Entity\ProductModelDraftInterface;
use PcmtProductBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtProductBundle\Service\ProductModelFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductModelDraftNormalizer extends DraftNormalizer implements NormalizerInterface
{
    /** @var ProductModelFromDraftCreator */
    private $productModelFromDraftCreator;

    /** @var ProductModelAttributeChangeService */
    protected $productModelAttributeChangeService;

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