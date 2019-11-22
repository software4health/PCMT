<?php

declare(strict_types=1);

namespace Pcmt\PcmtFamilyBundle;

use Pcmt\PcmtFamilyBundle\DependencyInjection\PcmtFamilyExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PcmtFamilyBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new PcmtFamilyExtension();
        }

        return $this->extension;
    }
}