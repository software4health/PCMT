<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Service\AttributeChange\ProductAttributeChangeService;
use PcmtDraftBundle\Service\Draft\ProductFromDraftCreator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ProductDraftNormalizer extends AbstractDraftNormalizer implements NormalizerInterface
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
        FormProviderInterface $formProvider,
        NormalizerInterface $productNormalizer
    ) {
        parent::__construct(
            $statusNormalizer,
            $attributeChangeNormalizer,
            $formProvider
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
        if (!$newProduct) {
            // that's a special case when a original product has been removed after creating a draft.
            return $data;
        }
        $data['label'] = $this->getLabel($draft, $newProduct);

        $changes = $this->productAttributeChangeService->get($newProduct, $draft->getProduct());
        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        $data['changes'] = $serializer->normalize($changes);

        $data['values'] = [
            'draftId'    => $draft->getId(),
            'identifier' => $newProduct->getIdentifier(),
            'family'     => $newProduct->getFamily() ? $newProduct->getFamily()->getCode() : '-',
            'parentId'   => $newProduct->getParent() ? $newProduct->getParent()->getId() : null,
            'parent'     => $newProduct->getParent() ? $newProduct->getParent()->getCode() : null,
        ];

        if ($context['include_product'] ?? false) {
            $data['product'] = $this->productNormalizer->normalize($newProduct, 'internal_api');
            $data['product']['meta']['form'] = $this->formProvider->getForm($draft);
        } else {
            $values = [];

            $copiedProduct = clone $newProduct;
            $copiedProduct->setParent(null);
            foreach ($copiedProduct->getValues() as $value) {
                /** @var ValueInterface $value */
                $values[$value->getAttributeCode()][] = $this->valuesNormalizer->normalize($value, 'standard');
            }

            $data['values']['values'] = $values;
        }

        return $data;
    }

    private function getLabel(ProductDraftInterface $draft, ProductInterface $newProduct): string
    {
        if ($draft instanceof ExistingProductDraft) {
            return $draft->getProduct()->getIdentifier() ?? '-';
        }

        return $newProduct->getIdentifier() ?? '-';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}