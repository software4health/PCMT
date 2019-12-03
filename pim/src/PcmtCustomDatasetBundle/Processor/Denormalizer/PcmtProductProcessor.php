<?php

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/*******************************************************************************
 * Copyright (c) 2013, Akeneo SAS
 * Copyright (c) 2019, VillageReach
 * Licensed under the Open Software License version 3.0 AND Non-Profit Open
 * Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0 AND OSL-3.0
 *******************************************************************************/

class PcmtProductProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var FindProductToImport */
    private $findProductToImport;

    /** @var AddParent */
    private $addParent;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var FilterInterface */
    protected $productFilter;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /** @var MediaStorer */
    private $mediaStorer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        FindProductToImport $findProductToImport,
        AddParent $addParent,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        FilterInterface $productFilter,
        AttributeFilterInterface $productAttributeFilter,
        MediaStorer $mediaStorer
    ) {
        parent::__construct($repository);

        $this->findProductToImport = $findProductToImport;
        $this->addParent = $addParent;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->detacher = $detacher;
        $this->productFilter = $productFilter;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->repository = $repository;
        $this->mediaStorer = $mediaStorer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item): ProductInterface
    {
        $itemHasStatus = isset($item['enabled']);
        if (!isset($item['enabled'])) {
            $item['enabled'] = $jobParameters = $this->stepExecution->getJobParameters()->get('enabled');
        }

        $identifier = $this->getIdentifier($item);

        if (null === $identifier) {
            $this->skipItemWithMessage($item, 'The identifier must be filled');
        }

        $parentProductModelCode = $item['parent'] ?? '';

        try {
            $familyCode = $this->getFamilyCode($item);
            $item['family'] = $familyCode;

            $item = $this->productAttributeFilter->filter($item);
            $filteredItem = $this->filterItemData($item);

            $product = $this->findProductToImport->fromFlatData($identifier, $familyCode);
        } catch (AccessDeniedException $e) {
            throw $this->skipItemAndReturnException($item, $e->getMessage(), $e);
        }

        if (false === $itemHasStatus && null !== $product->getId()) {
            unset($filteredItem['enabled']);
        }

        $jobParameters = $this->stepExecution->getJobParameters();
        $enabledComparison = $jobParameters->get('enabledComparison');
        if ($enabledComparison) {
            $filteredItem = $this->filterIdenticalData($product, $filteredItem);

            if (empty($filteredItem) && null !== $product->getId()) {
                $this->detachProduct($product);
                $this->stepExecution->incrementSummaryInfo('product_skipped_no_diff');

                return null;
            }
        }

        if ('' !== $parentProductModelCode && !$product->isVariant()) {
            try {
                $product = $this->addParent->to($product, $parentProductModelCode);
            } catch (\InvalidArgumentException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        if (isset($filteredItem['values'])) {
            try {
                $filteredItem['values'] = $this->mediaStorer->store($filteredItem['values']);
            } catch (InvalidPropertyException $e) {
                $this->detachProduct($product);
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        try {
            $this->updateProduct($product, $filteredItem);
        } catch (PropertyException $exception) {
            $this->detachProduct($product);
            $message = sprintf('%s: %s', $exception->getPropertyName(), $exception->getMessage());
            $this->skipItemWithMessage($item, $message, $exception);
        } catch (InvalidArgumentException | AccessDeniedException $exception) {
            $this->detachProduct($product);
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        return $product;
    }

    protected function filterIdenticalData(ProductInterface $product, array $filteredItem): array
    {
        return $this->productFilter->filter($product, $filteredItem);
    }

    protected function getIdentifier(array $item): ?string
    {
        return $item['identifier'] ?? null;
    }

    protected function getFamilyCode(array $item): string
    {
        if (array_key_exists('family', $item)) {
            return $item['family'];
        }

        $product = $this->repository->findOneByIdentifier($item['identifier']);
        if (null === $product) {
            return '';
        }

        $family = $product->getFamily();
        if (null === $family) {
            return '';
        }

        return $family->getCode();
    }

    /**
     * Filters item data to remove associations which are imported through a dedicated processor because we need to
     * create any products before to associate them
     */
    protected function filterItemData(array $item): array
    {
        foreach ($this->repository->getIdentifierProperties() as $identifierProperty) {
            unset($item['values'][$identifierProperty]);
        }
        unset($item['identifier'], $item['associations']);

        return $item;
    }

    /**
     * @throws PropertyException
     */
    protected function updateProduct(ProductInterface $product, array $filteredItem): void
    {
        $this->updater->update($product, $filteredItem);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateProduct(ProductInterface $product): ConstraintViolationListInterface
    {
        return $this->validator->validate($product);
    }

    /**
     * Detaches the product from the unit of work is the responsibility of the writer but in this case we
     * want ensure that an updated and invalid product will not be used in the association processor
     */
    protected function detachProduct(ProductInterface $product): void
    {
        $this->detacher->detach($product);
    }

    private function skipItemAndReturnException(array $item, string $message, ?\Throwable $previousException = null): InvalidItemException
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }
        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;
        $invalidItem = new FileInvalidItem($item, $itemPosition);

        return new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }
}
