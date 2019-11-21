<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductDraftSaver implements SaverInterface
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function save($draft, array $options = []): void
    {
        $this->validateDraft($draft);
        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($draft);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($draft, $options));
            $this->entityManager->flush();
            $this->entityManager->commit();
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($draft, $options));
        } catch (\Exception $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }
    }

    protected function validateDraft($draft): void
    {
        if (!$draft instanceof ProductDraftInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductDraftInterface::class,
                    get_class($draft)
                )
            );
        }

        if (!$draft->getId() && $draft->getProduct()) {
            $draftRepository = $this->entityManager->getRepository(AbstractProductDraft::class);
            $criteria = [
                'status' => AbstractProductDraft::STATUS_NEW,
                'product' => $draft->getProduct(),
            ];
            $count = $draftRepository->count($criteria);
            if ($count > 0) {
                throw new \InvalidArgumentException(
                    'There is already a draft for this product'
                );
            }
        }
    }
}