<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductDraftSaver implements SaverInterface
{
    /** @var EntityManagerInterface $entityManger */
    protected $entityManger;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManger = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function save($draft, array $options = []): void
    {
        $this->validateDraft($draft);
        $this->entityManger->beginTransaction();
        try{
            $this->entityManger->persist($draft);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($draft, $options));
            $this->entityManger->flush();
            $this->entityManger->commit();
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($draft, $options));
        } catch (\Exception $exception){
            $this->entityManger->rollback();
            throw $exception;
        }
    }

    protected function validateDraft($draft): void
    {
        if(!$draft instanceof ProductDraftInterface){
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductDraftInterface::class,
                    ClassUtils::getClass($draft)
                )
            );
        }
    }
}