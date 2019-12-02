<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Validator\Directory\Provider;

class ValidPathProvider
{
    public function getConfig(): array
    {
        return [
            'reference_data_files_path' => 'src/Pcmt/PcmtProductBundle/Resources/config/reference_data',
        ];
    }
}
