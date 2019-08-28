<?php
declare(strict_types=1);

namespace Pcmt\Bundle;

use Pcmt\Bundle\DependencyInjection\PcmtBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PcmtBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension){
            $this->extension = new PcmtBundleExtension();
        }

        return $this->extension;
    }
}