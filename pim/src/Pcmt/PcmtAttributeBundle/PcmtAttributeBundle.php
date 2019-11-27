<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle;

use Pcmt\PcmtAttributeBundle\DependencyInjection\PcmtAttributeExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PcmtAttributeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new PcmtAttributeExtension();
        }

        return $this->extension;
    }
}