<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Validators;

use PcmtRulesBundle\Constraints\DifferentFamilyConstraint;
use PcmtRulesBundle\Entity\Rule;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DifferentFamilyConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof DifferentFamilyConstraint) {
            throw new UnexpectedTypeException($constraint, DifferentFamilyConstraint::class);
        }

        /** @var Rule $entity */
        if (!$entity->getSourceFamily() || !$entity->getDestinationFamily()) {
            return;
        }

        if ($entity->getSourceFamily()->getId() === $entity->getDestinationFamily()->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('destination_family')
                ->addViolation();
        }
    }
}
