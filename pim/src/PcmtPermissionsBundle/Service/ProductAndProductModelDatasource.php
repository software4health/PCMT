<?php

declare(strict_types=1);

namespace PcmtPermissionsBundle\Service;

use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource as ProductAndProductModelDatasourceBase;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

/**
 * Product datasource for the product grid only.
 * It does not handle association grid, published product grid, etc.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelDatasource extends ProductAndProductModelDatasourceBase
{
    /** @var CategoryWithPermissionsRepository */
    private $categoryWithPermissionsRepository;

    public function setCategoryWithPermissionsRepository(CategoryWithPermissionsRepository $categoryWithPermissionsRepository): void
    {
        $this->categoryWithPermissionsRepository = $categoryWithPermissionsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $this->pqb->addFilter(
            'categories',
            'IN OR UNCLASSIFIED',
            $this->categoryWithPermissionsRepository->getCategoryCodes(CategoryPermissionsCheckerInterface::VIEW_LEVEL)
        );

        return parent::getResults();
    }
}
