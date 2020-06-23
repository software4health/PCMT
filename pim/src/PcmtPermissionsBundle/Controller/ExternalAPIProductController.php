<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController as AkeneoProductController;
use PcmtPermissionsBundle\Exception\NoCategoryAccessException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExternalAPIProductController extends AkeneoProductController
{
    /** {@inheritdoc} */
    public function deleteAction($code): Response
    {
        try {
            return parent::deleteAction($code);
        } catch (NoCategoryAccessException $e) {
            throw new AccessDeniedHttpException('No category permission');
        }
    }
}