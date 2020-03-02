<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PcmtCoreBundle\JobParameters\SupportedJobsTrait;
use Symfony\Component\Validator\Constraints\Collection;

class XmlReferenceDataImport implements ConstraintCollectionProviderInterface
{
    use SupportedJobsTrait;

    public function __construct(
        array $supportedJobNames
    ) {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'filePath' => null,
            ],
        ]);
    }
}
