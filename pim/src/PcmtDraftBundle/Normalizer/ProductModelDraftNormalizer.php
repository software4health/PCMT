<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtDraftBundle\Service\Draft\ProductModelFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductModelDraftNormalizer extends AbstractDraftNormalizer implements NormalizerInterface
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
        FormProviderInterface $formProvider,
        NormalizerInterface $productModelNormalizer
    ) {
        parent::__construct($statusNormalizer, $attributeChangeNormalizer, $formProvider);

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

        if (!$newProductModel) {
            // that's a special case when a original product has been removed after creating a draft.
            return $data;
        }
        $data['label'] = $this->getLabel($draft, $newProductModel);

        $changes = $this->productModelAttributeChangeService->get($newProductModel, $draft->getProductModel());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        $data['values'] = [
            'draftId'        => $draft->getId(),
            'code'           => $newProductModel->getCode(),
            'family'         => $newProductModel->getFamily()->getCode(),
            'family_variant' => $newProductModel->getFamilyVariant()->getCode(),
            'parentId'       => $newProductModel->getParent() ? $newProductModel->getParent()->getId() : null,
            'parent'         => $newProductModel->getParent() ? $newProductModel->getParent()->getCode() : null,
        ];

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productModelNormalizer->normalize($newProductModel, 'internal_api');
            $data['product']['meta']['form'] = $this->formProvider->getForm($draft);
        } else {
            $values = [];

            $copiedProduct = clone $newProductModel;
            $copiedProduct->setParent(null);
            foreach ($copiedProduct->getValues() as $value) {
                /** @var ValueInterface $value */
                $values[$value->getAttributeCode()][] = $this->valuesNormalizer->normalize($value, 'standard');
            }

            $data['values']['values'] = $values;
        }

        return $data;
    }

    private function getLabel(ProductModelDraftInterface $draft, ProductModelInterface $newProductModel): string
    {
        if ($draft instanceof ExistingProductModelDraft) {
            return $draft->getProductModel()->getCode() ?? '-';
        }

        return $newProductModel->getCode() ?? '-';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductModelDraftInterface;
    }
}