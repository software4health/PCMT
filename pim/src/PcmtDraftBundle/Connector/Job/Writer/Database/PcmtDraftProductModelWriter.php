<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelWriter;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductModelNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;

class PcmtDraftProductModelWriter extends ProductModelWriter implements PcmtDraftWriterInterface
{
    /** @var UserInterface */
    private $user;

    /** @var ProductModelNormalizer */
    private $productModelNormalizer;

    /** @var SaverInterface */
    private $draftSaver;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var EntityWithFamilyVariantAttributesProvider */
    protected $attributeProvider;

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function setProductModelNormalizer(ProductModelNormalizer $productModelNormalizer): void
    {
        $this->productModelNormalizer = $productModelNormalizer;
    }

    public function setProductModelDraftSaver(SaverInterface $productModelDraftSaver): void
    {
        $this->draftSaver = $productModelDraftSaver;
    }

    public function setProductModelFactory(SimpleFactoryInterface $productModelFactory): void
    {
        $this->productModelFactory = $productModelFactory;
    }

    public function setProductModelUpdater(ObjectUpdaterInterface $productModelUpdater): void
    {
        $this->productModelUpdater = $productModelUpdater;
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
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
        foreach ($items as $productModel) {
            try {
                $baseProductModel = $this->getProductModelOrCreateIfNotExists($productModel);
                $data = $this->prepareData($productModel);
                try {
                    $this->createDraft($baseProductModel, $data);
                } catch (\InvalidArgumentException $exception) {
                    throw $this->skipItemAndReturnException($data, $exception->getMessage(), $exception);
                }
                $this->incrementCount($productModel);
            } catch (InvalidItemException $exception) {
                $this->stepExecution->addWarning(
                    $exception->getMessage(),
                    $exception->getMessageParameters(),
                    $exception->getItem()
                );
            }
        }
    }

    private function getProductModelOrCreateIfNotExists(ProductModelInterface $item): ProductModelInterface
    {
        if ($item->getId()) {
            return $item;
        }

        return $this->createProductModel($item);
    }

    private function createProductModel(ProductModelInterface $productModel): ProductModelInterface
    {
        $productModel->setCreated(new \DateTime());
        $productModel->setUpdated(new \DateTime());
        $data = [
            'code'           => $productModel->getCode(),
            'family_variant' => $productModel->getFamilyVariant()->getCode(),
        ];
        if (!$productModel->isRoot()) {
            $productModelData = $this->productModelNormalizer->normalize($productModel, 'standard');
            $data['parent'] = $productModelData['parent'];

            $attributeSet = $productModel->getFamilyVariant()->getVariantAttributeSet(1);
            $axesAttributes = $attributeSet->getAxes();
            foreach ($axesAttributes as $attribute) {
                $code = $attribute->getCode();
                $data['values'][$code] = $productModelData['values'][$code];
            }
        }
        $newProductModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($newProductModel, $data);
        $this->productModelSaver->save($newProductModel);

        return $newProductModel;
    }

    private function prepareData(ProductModelInterface $productModel): array
    {
        $data = $this->productModelNormalizer->normalize($productModel, 'standard');
        if (!$productModel->isRoot()) {
            $data['values'] = $this->filterUnexpectedAttributes($productModel, $data['values']);
        }

        return $data;
    }

    private function createDraft(ProductModelInterface $productModel, array $data): void
    {
        $draft = new ExistingProductModelDraft(
            $productModel,
            $data,
            $this->user,
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );
        $this->draftSaver->save($draft);
    }

    protected function incrementCount(ProductModelInterface $productModel): void
    {
        $action = $productModel->getId() ? 'process' : 'create';
        $this->stepExecution->incrementSummaryInfo($action);
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

    private function filterUnexpectedAttributes(ProductModelInterface $productModel, array $values): array
    {
        $attributes = $this->attributeProvider->getAttributes($productModel);
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