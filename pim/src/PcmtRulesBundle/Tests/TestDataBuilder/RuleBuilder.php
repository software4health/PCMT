<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PcmtRulesBundle\Entity\Rule;

class RuleBuilder
{
    public const EXAMPLE_UNIQUE_ID = 'example_code';

    public const EXAMPLE_ID = 5;

    /** @var Rule */
    private $rule;

    public function __construct()
    {
        $this->rule = new Rule();

        $this->setId($this->rule, self::EXAMPLE_ID);

        $this->rule->setUniqueId(self::EXAMPLE_UNIQUE_ID);
        $this->rule->setSourceFamily((new FamilyBuilder())->build());
        $this->rule->setDestinationFamily((new FamilyBuilder())->build());
        $this->rule->setKeyAttribute((new AttributeBuilder())->build());
    }

    protected function setId(Rule $rule, int $value): void
    {
        $reflection = new \ReflectionClass(get_class($rule));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($rule, $value);
    }

    public function withKeyAttribute(?AttributeInterface $attribute): self
    {
        $this->rule->setKeyAttribute($attribute);

        return $this;
    }

    public function withSourceFamily(?FamilyInterface $family): self
    {
        $this->rule->setSourceFamily($family);

        return $this;
    }

    public function withDestinationFamily(?FamilyInterface $family): self
    {
        $this->rule->setDestinationFamily($family);

        return $this;
    }

    public function build(): Rule
    {
        return $this->rule;
    }
}