<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\DraftStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AbstractDraftNormalizer
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var AttributeChangeNormalizer */
    protected $attributeChangeNormalizer;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer,
        FormProviderInterface $formProvider
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
        $this->formProvider = $formProvider;
    }

    public function setValuesNormalizer(NormalizerInterface $valuesNormalizer): void
    {
        $this->valuesNormalizer = $valuesNormalizer;
    }

    public function normalize(DraftInterface $draft, ?string $format = null, array $context = []): array
    {
        $data = [];
        $data['id'] = $draft->getId();
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $data['updatedAt'] = $draft->getUpdatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author->getFirstName() . ' ' . $author->getLastName();
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
}