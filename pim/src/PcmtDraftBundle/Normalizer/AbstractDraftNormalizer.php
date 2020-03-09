<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\DraftStatus;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var PresenterInterface */
    private $datetimePresenter;

    /** @var UserContext */
    protected $userContext;

    /** @var TranslatorInterface */
    protected $translator;

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

    public function setDatetimePresenter(PresenterInterface $datetimePresenter): void
    {
        $this->datetimePresenter = $datetimePresenter;
    }

    public function normalize(DraftInterface $draft, ?string $format = null, array $context = []): array
    {
        if ($this->translator) {
            $context = array_merge($context, ['locale' => $this->translator->getLocale()]);
        }

        try {
            if ($this->userContext) {
                $timezone = $this->userContext->getUserTimezone();
                $datetimeContext = array_merge($context, ['timezone' => $timezone]);
            } else {
                $datetimeContext = $context;
            }
        } catch (\RuntimeException $exception) {
            $datetimeContext = $context;
        }

        $data = [];
        $data['id'] = $draft->getId();

        $data['createdAt'] = $this->datetimePresenter->present($draft->getCreatedAtFormatted(), $datetimeContext);
        $data['updatedAt'] = $this->datetimePresenter->present($draft->getUpdatedAtFormatted(), $datetimeContext);
        $author = $draft->getAuthor();
        $data['author'] = $author->getFirstName() . ' ' . $author->getLastName();
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);
        $data['type'] = ucfirst($draft->getType());
        $data['typeName'] = $this->getTypeName($draft->getType());
        $data['meta'] = [
            'id'                => $draft->getId(),
            'model_type'        => 'draft',
            'structure_version' => null,
        ];

        return $data;
    }

    public function getTypeName(string $type): string
    {
        switch ($type) {
            case NewProductDraft::TYPE:
                return 'pcmt.entity.draft.type.new_product_draft';
            case ExistingProductDraft::TYPE:
                return 'pcmt.entity.draft.type.existing_product_draft';
            case NewProductModelDraft::TYPE:
                return 'pcmt.entity.draft.type.new_product_model_draft';
            case ExistingProductModelDraft::TYPE:
                return 'pcmt.entity.draft.type.existing_product_model_draft';
        }
    }

    public function setUserContext(UserContext $userContext): void
    {
        $this->userContext = $userContext;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}