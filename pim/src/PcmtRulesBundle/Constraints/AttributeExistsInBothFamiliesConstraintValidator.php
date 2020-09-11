<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AttributeExistsInBothFamiliesConstraintValidator extends ConstraintValidator
{
    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    public function __construct(RuleAttributeProvider $ruleAttributeProvider)
    {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeExistsInBothFamiliesConstraint) {
            throw new UnexpectedTypeException($constraint, AttributeExistsInBothFamiliesConstraint::class);
        }

        /** @var Rule $entity */
        if (!$entity->getSourceFamily() || !$entity->getDestinationFamily() || !$entity->getKeyAttribute()) {
            return;
        }
        $attributes = $this->ruleAttributeProvider->getForFamilies($entity->getSourceFamily(), $entity->getDestinationFamily());
        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attribute */
            if ($entity->getKeyAttribute()->getCode() === $attribute->getCode()) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->atPath('key_attribute')
            ->addViolation();
    }
}
