<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductModelNormalizer implements NormalizerInterface
{
    /** @var ProductModelNormalizer */
    private $productModelNormalizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(ProductModelNormalizer $productModelNormalizer, EntityManagerInterface $entityManager)
    {
        $this->productModelNormalizer = $productModelNormalizer;
        $this->entityManager = $entityManager;
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

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->productModelNormalizer->supportsNormalization($data, $format);
    }
}