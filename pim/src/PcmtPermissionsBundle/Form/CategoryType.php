<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Form;

use Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType as BaseCategoryType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends BaseCategoryType
{
    /**
     * {@inheritdoc}
     *
     * * this can be extended to include further fields *
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }
}