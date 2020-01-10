<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\MassEditController;
use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\MassEditOperation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PcmtMassEditController extends MassEditController
{
    /**
     * {@inheritdoc}
     */
    public function launchAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ('change_family' === $data['operation']) {
            return new JsonResponse(null, 403);
        }
        $data = $this->operationConverter->convert($data);
        $operation = new MassEditOperation($data['jobInstanceCode'], $data['filters'], $data['actions']);
        $this->operationJobLauncher->launch($operation);

        return new JsonResponse();
    }
}
