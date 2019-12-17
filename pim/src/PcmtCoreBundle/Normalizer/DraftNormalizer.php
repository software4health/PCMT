<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer;

use PcmtCoreBundle\Entity\DraftInterface;
use PcmtCoreBundle\Entity\DraftStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftNormalizer implements NormalizerInterface
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var AttributeChangeNormalizer */
    protected $attributeChangeNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        /** @var DraftInterface $draft */
        $data = [];
        $data['id'] = $draft->getId();
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author ?
            $author->getFirstName() . ' ' . $author->getLastName() :
            'no author';
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);
        $data['type'] = ucfirst($draft->getType());
        $data['meta'] = [
            'id'                => $draft->getId(),
            'model_type'        => 'draft',
            'structure_version' => null,
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return false;
    }
}