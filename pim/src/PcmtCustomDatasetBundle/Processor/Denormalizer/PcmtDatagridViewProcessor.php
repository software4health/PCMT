<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Processor\Denormalizer;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\MissingIdentifierException;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Simple import processor
 *
 * @author    Julien Sanchez <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PcmtDatagridViewProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    public function __construct(
        DatagridViewRepository $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item): ?object
    {
        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $entity = $this->findOrCreateObject($itemIdentifier);

        try {
            $this->updater->update($entity, $item);
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validate($entity);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($itemIdentifier, $entity);
        }

        return $entity;
    }

    /**
     * @return mixed
     */
    protected function findOrCreateObject(string $itemIdentifier): ?object
    {
        $entity = $this->findOneByIdentifier($itemIdentifier);
        if (null === $entity) {
            return $this->createObject($itemIdentifier);
        }

        return $entity;
    }

    /**
     * Creates an empty new object to process.
     * We look first if there is already a processed item save in the execution context for the same identifier.
     */
    protected function createObject(string $itemIdentifier): object
    {
        if ('' === $itemIdentifier || null === $this->stepExecution) {
            return $this->factory->create();
        }

        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];

        return $processedItemsBatch[$itemIdentifier] ?? $this->factory->create();
    }

    /**
     * Validates the processed entity.
     */
    protected function validate(object $entity): ConstraintViolationListInterface
    {
        return $this->validator->validate($entity);
    }

    protected function saveProcessedItemInStepExecutionContext(string $itemIdentifier, object $processedItem): void
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }
    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier($repository, array $item): string
    {
        $properties = ['label'];
        $references = [];
        foreach ($properties as $property) {
            if (!isset($item[$property])) {
                throw new MissingIdentifierException(sprintf(
                    'Missing identifier column "%s". Columns found: %s.',
                    $property,
                    implode(', ', array_keys($item))
                ));
            }
            $references[] = $item[$property];
        }

        return implode('.', $references);
    }
    /**
     * Find an object according to its identifiers from a repository.
     *
     * @param mixed $repository the repository to search inside
     * @param array $data       the data that is currently processed
     *
     * @throws MissingIdentifierException in case the processed data do not allow to retrieve an object
     *                                    by its identifiers properly
     */
    protected function findObject($repository, array $data): ?object
    {
        // cannot declare $repository as strict_types=1 because of different type than parent
        $itemIdentifier = $this->getItemIdentifier($repository, $data);

        return $this->findOneByIdentifier($itemIdentifier);
    }

    protected function findOneByIdentifier(string $itemIdentifier): ?object
    {
        return $this->repository->findOneBy([
            'type'  => DatagridView::TYPE_PUBLIC,
            'label' => $itemIdentifier,
        ]);
    }
}
