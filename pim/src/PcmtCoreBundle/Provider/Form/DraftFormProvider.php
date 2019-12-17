<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Provider\Form;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Pcmt\PcmtCoreBundle\Entity\DraftInterface;

class DraftFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($product): string
    {
        return 'pcmt-product-drafts-edit';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof DraftInterface;
    }
}