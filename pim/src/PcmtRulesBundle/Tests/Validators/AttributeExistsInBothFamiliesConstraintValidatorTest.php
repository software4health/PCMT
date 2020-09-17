<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Validators;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeExistsInBothFamiliesConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PcmtRulesBundle\Validators\AttributeExistsInBothFamiliesConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeExistsInBothFamiliesConstraintValidatorTest extends TestCase
{
    /** @var RuleAttributeProvider|MockObject */
    private $ruleAttributeProviderMock;

    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    protected function setUp(): void
    {
        $this->ruleAttributeProviderMock = $this->createMock(RuleAttributeProvider::class);
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);
    }

    public function dataValidate(): array
    {
        $a1 = (new AttributeBuilder())->withCode('AAA1')->build();
        $a2 = (new AttributeBuilder())->withCode('AAA2')->build();

        return [
            [$a1, [$a1, $a2], true],
            [$a1, [$a2], false],
            [$a2, [$a2], true],
            [$a2, [], false],
            [null, [], true],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(?AttributeInterface $keyAttribute, array $attributes, bool $result): void
    {
        $this->ruleAttributeProviderMock->method('getForFamilies')->willReturn($attributes);
        $constraint = (new AttributeExistsInBothFamiliesConstraintBuilder())->build();
        $rule = (new RuleBuilder())->withKeyAttribute($keyAttribute)->build();

        if ($result) {
            $this->contextMock->expects($this->never())->method('buildViolation');
        } else {
            $this->contextMock->expects($this->once())->method('buildViolation')->willReturn($this->violationBuilderMock);
        }

        $validator = $this->getValidatorInstance();
        $validator->validate($rule, $constraint);
    }

    public function testValidateThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = $this->createMock(Constraint::class);
        $rule = (new RuleBuilder())->build();

        $validator = $this->getValidatorInstance();
        $validator->validate($rule, $constraint);
    }

    private function getValidatorInstance(): AttributeExistsInBothFamiliesConstraintValidator
    {
        $validator = new \PcmtRulesBundle\Validators\AttributeExistsInBothFamiliesConstraintValidator($this->ruleAttributeProviderMock);
        $validator->initialize($this->contextMock);

        return $validator;
    }
}