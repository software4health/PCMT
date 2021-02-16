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

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Constraints\CorrectAttributeMappingConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CorrectAttributeMappingConstraintValidator extends ConstraintValidator
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public function __construct(
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof CorrectAttributeMappingConstraint) {
            throw new UnexpectedTypeException($constraint, CorrectAttributeMappingConstraint::class);
        }

        $sourceFamilyCode = $this->getStringValueFromRoot('sourceFamily');
        $destinationFamilyCode = $this->getStringValueFromRoot('destinationFamily');

        $sourceFamily = $this->getFamilyByCode($sourceFamilyCode);
        $destinationFamily = $this->getFamilyByCode($destinationFamilyCode);

        if (!$sourceFamily || !$destinationFamily) {
            return;
        }

        try {
            $attributeMapping = $this->getArrayValueFromRoot('attributeMapping');
            foreach ($attributeMapping as $row) {
                $this->validateRow($sourceFamily, $destinationFamily, $row['sourceValue'], $row['destinationValue']);
            }
        } catch (\Throwable $e) {
            $this->context->buildViolation($constraint->message. ' | '. $e->getMessage())
                ->addViolation();
        }
    }

    private function validateRow(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        string $sourceAttributeCode,
        string $destinationAttributeCode
    ): void {
        if (!$sourceAttributeCode && !$destinationAttributeCode) {
            return;
        }
        if (!$sourceFamily->hasAttributeCode($sourceAttributeCode)) {
            throw new \Exception('Source family does not have attribute '. $sourceAttributeCode);
        }
        if (!$destinationFamily->hasAttributeCode($destinationAttributeCode)) {
            throw new \Exception('Destination family does not have attribute '. $destinationAttributeCode);
        }
        $sourceAttributes = $sourceFamily->getAttributes();
        /** @var AttributeInterface $sourceAttribute */
        $sourceAttribute = $sourceAttributes->filter(function (AttributeInterface $attribute) use ($sourceAttributeCode) {
            return $attribute->getCode() === $sourceAttributeCode;
        })->first();
        $destinationAttributes = $destinationFamily->getAttributes();
        /** @var AttributeInterface $destinationAttribute */
        $destinationAttribute = $destinationAttributes->filter(function (AttributeInterface $attribute) use ($destinationAttributeCode) {
            return $attribute->getCode() === $destinationAttributeCode;
        })->first();
        if ($sourceAttribute->getType() !== $destinationAttribute->getType()) {
            throw new \Exception(sprintf(
                'Attributes %s and %s are of different types.',
                $sourceAttribute->getLabel(),
                $destinationAttribute->getLabel()
            ));
        }
    }

    private function getStringValueFromRoot(string $code): string
    {
        $root = $this->context->getRoot();

        return $root[$code] ?? '';
    }

    private function getArrayValueFromRoot(string $code): array
    {
        $root = $this->context->getRoot();

        return $root[$code] ?? [];
    }

    private function getFamilyByCode(string $code): ?FamilyInterface
    {
        return $this->familyRepository->findOneBy(['code' => $code]);
    }
}
