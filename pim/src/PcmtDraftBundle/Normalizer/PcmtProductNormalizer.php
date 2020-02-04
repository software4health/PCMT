<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductNormalizer implements NormalizerInterface
{
    /** @var ProductNormalizer */
    private $productNormalizer;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(ProductNormalizer $productNormalizer, EntityManagerInterface $entityManager)
    {
        $this->productNormalizer = $productNormalizer;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        /** @var ProductInterface $product */
        $data = $this->productNormalizer->normalize($product, $format, $context);

        if ($context['include_draft_id'] ?? false) {
            $draft = $this->entityManager->getRepository(AbstractDraft::class)->findOneBy(
                [
                    'product' => $product,
                    'status'  => AbstractDraft::STATUS_NEW,
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
        return $this->productNormalizer->supportsNormalization($data, $format);
    }
}