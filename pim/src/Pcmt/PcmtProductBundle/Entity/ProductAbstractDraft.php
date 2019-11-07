<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Carbon\Carbon;


abstract class ProductAbstractDraft implements ProductDraftInterface
{
    /** @var int $id */
    protected $id;

    /** @var \DateTime $created */
    protected $created;

    /** @var \DateTime $updated */
    protected $updated;

    /** @var \DateTime $approved */
    protected $approved;

    /** @var int $version */
    protected $version;

    /** @var int $status */
    protected $status;

    /** @var UserInterface $author */
    protected $author;

    /** @var UserInterface $updatedBy */
    protected $updatedBy;

    /** @var UserInterface $approvedBy */
    protected $approvedBy;

    /** @var array $draftHistoryEntries */
    protected $draftHistoryEntries;

    // keep product-related data here.
    // like family, groups etc. all the fields. - it helps rebuild product from new draft.
    /** @var array $productData */
    protected $productData;

    /** @var ProductInterface|null $product */
    protected $product;

    protected function __construct
    (
       UserInterface $author,
       \DateTime $created,
       int $version,
       int $status
    )
    {
        $this->author = $author;
        $this->created = $created;
        $this->version = $version;
        $this->status = $status;
        $this->draftHistoryEntries = new ArrayCollection();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function addDraftHistory(DraftHistoryInterface $draftHistory): void
    {
        if($this->draftHistoryEntries->contains($draftHistory)){
            return;
        }
        $this->draftHistoryEntries->add($draftHistory);
        $draftHistory->setDraft($this);
    }

    public function getDraftHistoryEntries(): Collection
    {
        return $this->draftHistoryEntries;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created;
    }

    public function getCreatedAtFormatted(): string
    {
        return Carbon::parse($this->created)->isoFormat('LLLL');
    }

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function getProductData(): ?array
    {
        return $this->productData;
    }

    public function approve(): self
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved = new \DateTime();

        return $this;
    }

    abstract public function getType(): string;

    abstract public function nextVersion(): void;
}