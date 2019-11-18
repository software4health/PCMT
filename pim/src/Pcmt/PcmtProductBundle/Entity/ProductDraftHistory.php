<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * class represents unit of change to the draft
 */
class ProductDraftHistory implements DraftHistoryInterface
{
    /** @var int $id */
    protected $id;

    /** @var ProductDraftInterface $draft */
    protected $draft;

    /** @var array $changeSet */
    protected $changeSet;

    /** @var \DateTime $createdAt */
    protected $createdAt;

    /** @var UserInterface $author */
    protected $author;

    public function __construct(
        \DateTime $createdAt,
        UserInterface $author,
        array $changeSet = []
    )
    {
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->changeSet = $changeSet;
    }

    public function setChangeSet(array $changeSet): void
    {
        $this->changeSet = $changeSet;
    }

    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    public function setDraft(ProductDraftInterface $draft): void
    {
        $this->draft = $draft;
    }

    public function getDraft(): ProductDraftInterface
    {
        return $this->draft;
    }

    public function setAuthor(UserInterface $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}