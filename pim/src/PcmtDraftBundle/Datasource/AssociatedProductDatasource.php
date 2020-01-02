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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductDatasource as AssociatedProductDatasourceOriginal;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Draft\ProductFromDraftCreator;

/**
 * Original class copyrights:
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociatedProductDatasource extends AssociatedProductDatasourceOriginal
{
    /** @var ProductFromDraftCreator */
    protected $creator;

    public function setCreator(ProductFromDraftCreator $creator): void
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
        if (!$sourceProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductInterface::class);
        }

        $repo = $this->om->getRepository(AbstractDraft::class);
        $criteria = [
            'status'  => AbstractDraft::STATUS_NEW,
            'product' => $sourceProduct->getId(),
        ];
        $draft = $repo->findOneBy($criteria);

        if (!$draft instanceof DraftInterface) {
            throw InvalidObjectException::objectExpected($draft, DraftInterface::class);
        }

        $sourceProduct = $this->creator->getProductToSave($draft);

        /** @var ProductModelAssociation $association */
        $association = $this->getAssociation($sourceProduct, (int) $this->getConfiguration('association_type_id'));
        if (null === $association) {
            return [
                'totalRecords' => 0,
                'data'         => [],
            ];
        }

        $associatedProductsIds = $this->getAssociatedProductIds($association);
        $associatedProductModelsIds = $this->getAssociatedProductModelIds($association);

        $limit = (int) $this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $associatedProductsIdsFromParent = [];
        $associatedProductModelsIdsFromParent = [];
        $parentAssociation = $this->getParentAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null !== $parentAssociation) {
            $associatedProductsIdsFromParent = $this->getAssociatedProductIds($parentAssociation);
            $associatedProductModelsIdsFromParent = $this->getAssociatedProductModelIds($parentAssociation);
        }

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIds,
            $limit,
            $from,
            $locale,
            $scope
        );

        $normalizedAssociatedProducts = $this->normalizeProductsAndProductModels(
            $associatedProducts,
            $associatedProductsIdsFromParent,
            $locale,
            $scope
        );

        $productModelLimit = $limit - $associatedProducts->count();
        $normalizedAssociatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIds) + $associatedProducts->count();
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIds,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );

            $normalizedAssociatedProductModels = $this->normalizeProductsAndProductModels(
                $associatedProductModels,
                $associatedProductModelsIdsFromParent,
                $locale,
                $scope
            );
        }

        $rows = ['totalRecords' => count($associatedProductsIds) + count($associatedProductModelsIds)];
        $rows['data'] = array_merge($normalizedAssociatedProducts, $normalizedAssociatedProductModels);

        return $rows;
    }

    private function getAssociation(ProductInterface $sourceProduct, int $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === $associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
