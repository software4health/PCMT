<?php

declare(strict_types=1);

/**
 * New associated product datasource.
 * Overrides the original one so that we can show associated products for draft,
 * not for original product.
 *
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
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
class AssociatedProductModelDatasource extends OriginalAssociatedProductModelDatasource
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

        return $this->getResultsForProductModel($sourceProduct);
    }
}
