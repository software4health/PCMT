<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\Validator\Directory\Provider;

class ValidPathProvider
{
    public function getConfig()
    {
        return [
            'reference_data_files_path' => 'src/Pcmt/Bundle/PcmtConnectorBundle/Resources/config/reference_data'
        ];
    }
}