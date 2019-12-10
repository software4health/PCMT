<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\AbstractProductModelDraft;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductModelDraftSaver implements SaverInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
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
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }
    }

    protected function validateDraft(object $draft): void
    {
        if (!$draft instanceof ProductModelDraftInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductModelDraftInterface::class,
                    get_class($draft)
                )
            );
        }

        if (!$draft->getId() && $draft->getProductModel()) {
            $draftRepository = $this->entityManager->getRepository(AbstractProductModelDraft::class);
            $criteria = [
                'status'       => AbstractDraft::STATUS_NEW,
                'productModel' => $draft->getProductModel(),
            ];
            $count = $draftRepository->count($criteria);
            if ($count > 0) {
                throw new \InvalidArgumentException(
                    'There is already a draft for this product model'
                );
            }
        }
    }
}