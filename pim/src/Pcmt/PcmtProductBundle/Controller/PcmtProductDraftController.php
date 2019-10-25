<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;

class PcmtProductDraftController
{
    public function __construct()
    {
    }

    /** approve existig draft */
    public function approveAction(Request $request): JsonResponse
    {
        throw new NotImplementedException('method not impemented');
    }
}