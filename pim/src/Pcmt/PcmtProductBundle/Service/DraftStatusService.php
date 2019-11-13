<?php

namespace Pcmt\PcmtProductBundle\Service;

use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DraftStatusService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {

        $this->translator = $translator;
    }

    public function getAll()
    {
        return [
            ProductDraftInterface::STATUS_NEW,
            ProductDraftInterface::STATUS_APPROVED,
            ProductDraftInterface::STATUS_REJECTED,
        ];
    }

    public function getNameTranslated($statusId): string
    {
        return $this->translator->trans($this->getName($statusId));
    }

    public function getName($statusId): string
    {
        switch ($statusId) {
            case ProductDraftInterface::STATUS_NEW :
                return 'pcmt_product.draft.status_new';
            case ProductDraftInterface::STATUS_APPROVED :
                return 'pcmt_product.draft.status_approved';
            case ProductDraftInterface::STATUS_REJECTED :
                return 'pcmt_product.draft.status_rejected';
            default:
                throw new \Exception("No draft status name for: " . $statusId);
        }
    }

}