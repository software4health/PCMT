<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Validators;

use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Tests\TestDataBuilder\DifferentFamilyConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PcmtRulesBundle\Validators\DifferentFamilyConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DifferentFamilyConstraintValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);
    }

    public function dataValidate(): array
    {
        $family1 = (new FamilyBuilder())->withCode('xxx')->withId(1)->build();
        $family2 = (new FamilyBuilder())->withCode('yyy')->withId(2)->build();

        $rule1 = (new RuleBuilder())->withSourceFamily($family1)->withDestinationFamily($family2)->build();
        $rule2 = (new RuleBuilder())->withSourceFamily($family1)->withDestinationFamily($family1)->build();
        $rule3 = (new RuleBuilder())->withSourceFamily($family2)->withDestinationFamily($family2)->build();
        $rule4 = (new RuleBuilder())->withSourceFamily($family2)->withDestinationFamily(null)->build();

        return [
            [$rule1, true],
            [$rule2, false],
            [$rule3, false],
            [$rule4, true],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(Rule $rule, bool $result): void
    {
        $constraint = (new DifferentFamilyConstraintBuilder())->build();

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

    private function getValidatorInstance(): DifferentFamilyConstraintValidator
    {
        $validator = new DifferentFamilyConstraintValidator();
        $validator->initialize($this->contextMock);

        return $validator;
    }
}