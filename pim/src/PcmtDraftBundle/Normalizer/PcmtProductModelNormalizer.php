<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Helper\UnexpectedAttributesFilter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductModelNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $productModelNormalizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UnexpectedAttributesFilter */
    private $attributesFilter;

    public function __construct(
        NormalizerInterface $productModelNormalizer,
        EntityManagerInterface $entityManager,
        UnexpectedAttributesFilter $attributesFilter
    ) {
        $this->productModelNormalizer = $productModelNormalizer;
        $this->entityManager = $entityManager;
        $this->attributesFilter = $attributesFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = []): array
    {
        /** @var ProductModelInterface $productModel */
        $data = $this->productModelNormalizer->normalize($productModel, $format, $context);

        if ($context['include_draft_id'] ?? false) {
            $draft = $this->entityManager->getRepository(AbstractDraft::class)->findOneBy(
                [
                    'productModel' => $productModel,
                    'status'       => AbstractDraft::STATUS_NEW,
                ]
            );

            $data['draftId'] = $draft ? $draft->getId() : 0;
        }

        if ($this->hasToFilterUnexpectedValues($context, $productModel, $data)) {
            $data['values'] = $this->attributesFilter->filter($productModel, $data['values']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->productModelNormalizer->supportsNormalization($data, $format);
    }

    private function hasToFilterUnexpectedValues(array $context, ProductModelInterface $productModel, array $data): bool
    {
        return ($context['import_via_drafts'] ?? false)
            && !$productModel->isRoot()
            && isset($data['values']);
    }
}