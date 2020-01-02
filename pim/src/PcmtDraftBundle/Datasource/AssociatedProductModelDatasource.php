<?php

declare(strict_types=1);

/**
 * New associated product datasource.
 * Overrides the original one so that we can show associated products from draft,
 * not from original product.
 *
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductModelDatasource as AssociatedProductModelDatasourceOriginal;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Draft\ProductModelFromDraftCreator;

/**
 * Product datasource dedicated to the product association datagrid.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociatedProductModelDatasource extends AssociatedProductModelDatasourceOriginal
{
    /** @var ProductModelFromDraftCreator */
    protected $creator;

    public function setCreator(ProductModelFromDraftCreator $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * The original product from database is overridden by the product created from draft.
     * In that way, we can correctly get associations for draft product, not for the original one.
     *
     * {@inheritdoc}
     */
    public function getResults()
    {
        $sourceProduct = $this->getConfiguration('current_product', false);
        if (!$sourceProduct instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductModelInterface::class);
        }

        $repo = $this->om->getRepository(AbstractDraft::class);
        $criteria = [
            'status'       => AbstractDraft::STATUS_NEW,
            'productModel' => $sourceProduct->getId(),
        ];
        $draft = $repo->findOneBy($criteria);

        if (!$draft instanceof DraftInterface) {
            throw InvalidObjectException::objectExpected($draft, DraftInterface::class);
        }

        $sourceProduct = $this->creator->getProductModelToSave($draft);

        $association = $this->getAssociation($sourceProduct, (int) $this->getConfiguration('association_type_id'));
        if (null === $association) {
            return [
                'totalRecords' => 0,
                'data'         => [],
            ];
        }

        $associatedProductsIdentifiers = $this->getAssociatedProductIdentifiers($association);
        $associatedProductModelsIdentifiers = $this->getAssociatedProductModelIdentifiers($association);

        $limit = (int) $this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $associatedProductsIdentifiersFromParent = [];
        $associatedProductModelsIdentifiersFromParent = [];
        $parentAssociation = $this->getParentAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null !== $parentAssociation) {
            $associatedProductsIdentifiersFromParent = $this->getAssociatedProductIdentifiers($parentAssociation);
            $associatedProductModelsIdentifiersFromParent = $this->getAssociatedProductModelIdentifiers($parentAssociation);
        }

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIdentifiers,
            $limit,
            $from,
            $locale,
            $scope
        );

        $productModelLimit = $limit - $associatedProducts->count();
        $associatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIdentifiers) + $associatedProducts->count();
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIdentifiers,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );
        }

        $normalizedAssociatedProducts = $this->normalizeProductsAndProductModels(
            $associatedProducts,
            $associatedProductsIdentifiersFromParent,
            $locale,
            $scope
        );

        $normalizedAssociatedProductModels = $this->normalizeProductsAndProductModels(
            $associatedProductModels,
            $associatedProductModelsIdentifiersFromParent,
            $locale,
            $scope
        );

        $rows = ['totalRecords' => count($associatedProductsIdentifiers) + count($associatedProductModelsIdentifiers)];
        $rows['data'] = array_merge($normalizedAssociatedProducts, $normalizedAssociatedProductModels);

        return $rows;
    }

    private function getAssociation(EntityWithAssociationsInterface $sourceProduct, int $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === $associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
