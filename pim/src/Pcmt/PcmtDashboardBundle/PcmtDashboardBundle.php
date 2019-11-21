<?php

declare(strict_types=1);

namespace Pcmt\PcmtDashboardBundle;

use Pcmt\PcmtDashboardBundle\DependencyInjection\PcmtDashboardExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PcmtDashboardBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new PcmtDashboardExtension();
    }
}