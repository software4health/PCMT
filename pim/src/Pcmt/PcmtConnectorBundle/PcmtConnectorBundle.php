<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle;

use Pcmt\PcmtConnectorBundle\DependencyInjection\PcmtConnectorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PcmtConnectorBundle extends Bundle
{
    public function getContainerExtension()
    {
        if(null === $this->extension){
            $extension = new PcmtConnectorExtension();
        }

        return $extension;
    }
}