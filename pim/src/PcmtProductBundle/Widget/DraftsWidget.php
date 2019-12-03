<?php

declare(strict_types=1);

namespace PcmtProductBundle\Widget;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;

class DraftsWidget implements WidgetInterface
{
    /** @var DraftsFetcher */
    protected $draftsFetcher;

    public function __construct(DraftsFetcher $draftsFetcher)
    {
        $this->draftsFetcher = $draftsFetcher;
    }

    public function getAlias(): string
    {
        return 'draft_products_overview';
    }

    public function getTemplate(): string
    {
        return 'PcmtProductBundle:Widget:draft_products_overview.html.twig';
    }

    public function getParameters(): array
    {
        return [];
    }

    public function getData(): array
    {
        return $this->draftsFetcher->fetch();
    }
}