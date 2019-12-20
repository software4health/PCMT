<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\AttributeChange;

use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;

class FakeAttributeChangeService extends AttributeChangeService
{
    /**
     * {@inheritdoc}
     */
    public function createChange(string $attribute, $value, $previousValue): void
    {
        parent::createChange($attribute, $value, $previousValue);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}