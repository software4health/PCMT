<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Manager;

use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Symfony\Component\Intl\Exception\NotImplementedException;

class DraftManager
{
    private $updaters;

    private $approver;

    public function __construct()
    {
        $this->updaters = [];
    }

    // find services tagged draft_updater
    public function addDraftUpdater(ObjectUpdaterInterface $objectUpdater): void
    {
        $this->updaters[] = $objectUpdater;
    }

    public function approveDraft(ProductDraftInterface $draft): void
    {
        switch(get_class($draft)){
            case NewProductDraft::class:
                $this->approver = new NewDraftApprover();
                break;
            case PendingProductDraft::class:
                $this->approver = new PendingDraftApprover();
                break;
            default:
                throw new \Exception('Draft is of an unknown type, cannot update');
        }

        $this->approver->approve($draft);
    }

    protected function getUpdater(string $type): ObjectUpdaterInterface
    {
        throw new NotImplementedException('method not implemented');
    }
}
