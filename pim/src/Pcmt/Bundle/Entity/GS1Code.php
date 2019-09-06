<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Entity;

use Pim\Bundle\CustomEntityBundle\Entity\AbstractCustomEntity;

abstract class GS1Code extends AbstractCustomEntity
{
    /** @var string $listName */
    protected $listName;

    /** @var string $name */
    protected $name;

    /** @var string $definition */
    protected $definition;

    /** @var int $version */
    protected $version;

    /** @var \DateTime $changeDate */
    protected $changeDate;

    /** @var int $status */
    protected $status = 1;

    public function __construct()
    {
        $this->setListName(static::getClass());
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getListName(): string
    {
        return $this->listName;
    }

    /**
     * @param string $listName
     */
    protected function setListName(string $listName): void
    {
        $this->listName = $listName;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $codeName
     */
    public function setName(?string $codeName): void
    {
        $this->name = $codeName;
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @param string $definition
     */
    public function setDefinition(?string $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * @param \DateTime $changeDate
     */
    public function setChangeDate(\DateTime $changeDate): void
    {
        $this->changeDate = $changeDate;
    }

    /**
     * @return \DateTime
     */
    public function getChangeDate(): \DateTime
    {
        return $this->changeDate;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    public function getType(): string
    {
        return 'gs1_code';
    }

    public function getCustomEntityName(): string
    {
        return static::getClass();
    }

    abstract public function getReferenceDataEntityType(): string ;

    abstract protected static function getClass(): string;
}