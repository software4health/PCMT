<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Updater\DatagridViewUpdater;

/**
 * Update the datagrid view properties
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PcmtDatagridViewUpdater extends DatagridViewUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function update($datagridView, array $data, array $options = []): ObjectUpdaterInterface
    {
        if (!$datagridView instanceof DatagridView) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($datagridView),
                DatagridView::class
            );
        }
        $user = $this->userRepository->findOneByIdentifier($data['owner']);
        if (null !== $user) {
            foreach ($data as $field => $value) {
                $this->setData($datagridView, $field, $value);
            }
        }

        return $this;
    }
}
