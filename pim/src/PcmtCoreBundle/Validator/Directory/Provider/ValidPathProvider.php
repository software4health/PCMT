<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Validator\Directory\Provider;

class ValidPathProvider
{
    public function getConfig(): array
    {
        return [
            'reference_data_files_path' => 'src/PcmtCoreBundle/Resources/config/reference_data',
        ];
    }
}
