<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Pcmt\PcmtProductBundle\Entity\ExistingProductModelDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductModelDraft;
use Pcmt\PcmtProductBundle\Entity\ProductModelDraftInterface;
use Pcmt\PcmtProductBundle\Service\ProductModelAttributeChangeService;
use Pcmt\PcmtProductBundle\Service\ProductModelFromDraftCreator;
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
        $productLabel = '';
        /** @var ProductModelDraftInterface $draft */
        switch (get_class($draft)) {
            case NewProductModelDraft::class:
                $productLabel = $newProductModel->getCode();
                break;
            case ExistingProductModelDraft::class:
                $productLabel = $draft->getProductModel()->getCode();
                break;
        }
        $data['label'] = $productLabel;

        $changes = $this->productModelAttributeChangeService->get($newProductModel, $draft->getProductModel());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductModelDraftInterface;
    }
}