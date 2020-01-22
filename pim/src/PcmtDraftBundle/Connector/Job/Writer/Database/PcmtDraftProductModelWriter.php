<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductModelNormalizer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;

class PcmtDraftProductModelWriter extends ProductModelWriter
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

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
        foreach ($items as $productModel) {
            $newProductModel = null;
            if ($productModel->getId()) {
                $newProductModel = $productModel;
            } else {
                $data = [
                    'code'           => $productModel->getCode(),
                    'family_variant' => $productModel->getFamilyVariant()->getCode(),
                ];
                $newProductModel = $this->createProductModel($data);
                $this->productModelSaver->save($newProductModel);
                $productModel->setCreated(new \DateTime());
                $productModel->setUpdated(new \DateTime());
            }
            if (null !== $newProductModel) {
                $data = $this->productModelNormalizer->normalize($productModel, 'standard');
                $draft = new ExistingProductModelDraft(
                    $newProductModel,
                    $data,
                    $this->user,
                    new \DateTime(),
                    AbstractDraft::STATUS_NEW
                );
                $this->draftSaver->save($draft);
            }
            $this->incrementCount($productModel);
        }
    }

    private function createProductModel(array $data): object
    {
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, $data);

        return $productModel;
    }

    protected function incrementCount(ProductModelInterface $productModel): void
    {
        $action = $productModel->getId() ? 'process' : 'create';
        $this->stepExecution->incrementSummaryInfo($action);
    }
}