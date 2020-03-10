<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\PimDataGridBundle\Updater\DatagridViewUpdater as BaseDatagridViewUpdater;
use PcmtCustomDatasetBundle\Exception\UserMissingException;

/**
 * Update the datagrid view properties
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewUpdater extends BaseDatagridViewUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function update($datagridView, array $data, array $options = []): ObjectUpdaterInterface
    {
        $user = $this->userRepository->findOneByIdentifier($data['owner']);
        if (null === $user) {
            throw new UserMissingException($data['owner']);
        }

        return parent::update($datagridView, $data, $options);
    }
}
