<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Symfony\Component\Translation\TranslatorInterface;

class DraftStatusTranslatorService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getNameTranslated($statusId): string
    {
        return $this->translator->trans($this->getName($statusId));
    }

    public function getName($statusId): string
    {
        switch ($statusId) {
            case AbstractProductDraft::STATUS_NEW:
                return 'pcmt_product.draft.status_new';
            case AbstractProductDraft::STATUS_APPROVED:
                return 'pcmt_product.draft.status_approved';
            case AbstractProductDraft::STATUS_REJECTED:
                return 'pcmt_product.draft.status_rejected';
            default:
                throw new \Exception('No draft status name for: ' . $statusId);
        }
    }
}