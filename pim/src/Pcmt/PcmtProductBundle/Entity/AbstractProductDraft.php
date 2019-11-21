<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Carbon\Carbon;


abstract class AbstractProductDraft implements ProductDraftInterface
{
    public const STATUS_NEW = 1;
    public const STATUS_REJECTED = 4;
    public const STATUS_APPROVED = 2;

    /** @var int $id */
    protected $id = 0;

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

    // keep product-related data here.
    // like family, groups etc. all the fields. - it helps rebuild product from new draft.
    /** @var array $productData */
    protected $productData;

    /** @var ProductInterface|null $product */
    protected $product;

    protected function __construct(
        UserInterface $author,
        \DateTime $created,
        int $status
    )
    {
        $this->author = $author;
        $this->created = $created;
        $this->status = $status;
        $this->version = AbstractProductDraft::DRAFT_VERSION_NEW;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $statusId): void
    {
        $this->status = $statusId;
    }

    /**
     * @param \DateTime $approved
     */
    public function setApproved(\DateTime $approved): void
    {
        $this->approved = $approved;
    }

    /**
     * @param UserInterface $approvedBy
     */
    public function setApprovedBy(UserInterface $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    abstract public function getType(): string;
}