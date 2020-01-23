<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductDraft;

class PcmtDraftProductWriter extends ProductWriter
{
    /** @var UserInterface */
    private $user;

    /** @var ProductNormalizer */
    private $productNormalizer;

    /** @var SaverInterface */
    private $draftSaver;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var EntityWithFamilyVariantAttributesProvider */
    protected $attributeProvider;

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function setProductNormalizer(ProductNormalizer $productNormalizer): void
    {
        $this->productNormalizer = $productNormalizer;
    }

    public function setProductDraftSaver(SaverInterface $productDraftSaver): void
    {
        $this->draftSaver = $productDraftSaver;
    }

    public function setProductSaver(SaverInterface $productSaver): void
    {
        $this->productSaver = $productSaver;
    }

    public function setProductBuilder(ProductBuilderInterface $productBuilder): void
    {
        $this->productBuilder = $productBuilder;
    }

    public function setAttributeProvider(EntityWithFamilyVariantAttributesProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        foreach ($items as $item) {
            try {
                $product = $this->getProductOrCreateIfNotExists($item);
                $data = $this->prepareData($item);

                try {
                    $this->createDraft($product, $data);
                } catch (\InvalidArgumentException $exception) {
                    throw $this->skipItemAndReturnException($data, $exception->getMessage(), $exception);
                }

                $this->incrementCount($item);
            } catch (InvalidItemException $exception) {
                $this->stepExecution->addWarning(
                    $exception->getMessage(),
                    $exception->getMessageParameters(),
                    $exception->getItem()
                );
            }
        }
    }

    private function getProductOrCreateIfNotExists(ProductInterface $item): ProductInterface
    {
        if ($item->getId()) {
            return $item;
        }

        return $this->createProduct($item);
    }

    private function createProduct(ProductInterface $item): ProductInterface
    {
        $product = $this->productBuilder->createProduct($item->getIdentifier(), $item->getFamily()->getCode());
        $this->productSaver->save($product);

        return $product;
    }

    private function prepareData(ProductInterface $product): array
    {
        $data = $this->productNormalizer->normalize($product, 'standard');

        if ($product->isVariant()) {
            $data['values'] = $this->filterUnexpectedAttributes($product, $data['values']);
        }

        return $data;
    }

    private function createDraft(ProductInterface $product, array $data): void
    {
        $draft = new ExistingProductDraft(
            $product,
            $data,
            $this->user,
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );
        $this->draftSaver->save($draft);
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

    private function filterUnexpectedAttributes(ProductInterface $product, array $values): array
    {
        $attributes = $this->attributeProvider->getAttributes($product);
        $levelAttributeCodes = array_map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            },
            $attributes
        );

        return array_filter($values, function ($key) use ($levelAttributeCodes) {
            return in_array($key, $levelAttributeCodes);
        }, ARRAY_FILTER_USE_KEY);
    }
}