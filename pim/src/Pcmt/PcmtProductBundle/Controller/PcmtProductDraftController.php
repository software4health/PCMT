<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;

class PcmtProductDraftController
{
    public function __construct()
    {
    }

    /**
     * approve existig draft
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveAction(Request $request): JsonResponse
    {
        throw new NotImplementedException('method not impemented');
    }
}