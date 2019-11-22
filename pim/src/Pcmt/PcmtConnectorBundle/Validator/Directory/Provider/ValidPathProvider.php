<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Validator\Directory\Provider;

class ValidPathProvider
{
    public function getConfig()
    {
        return [
            'reference_data_files_path' => 'src/Pcmt/PcmtConnectorBundle/Resources/config/reference_data',
        ];
    }
}
