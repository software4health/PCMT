<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Updater;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtRulesBundle\Entity\Rule;

class RuleUpdater implements ObjectUpdaterInterface
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'uniqueId'   => 'unique id string',
     *     'sourceFamily' => 'sourceFamilyCode',
     *     'destinationFamily' => 'destinationFamilyCode',
     * ]
     */
    public function update($rule, array $data, array $options = [])
    {
        if (!$rule instanceof Rule) {
            throw InvalidObjectException::objectExpected(
                get_class($rule),
                Rule::class
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($rule, $field, $item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function setData(Rule $rule, $field, $data): void
    {
        switch ($field) {
            case 'unique_id':
                $rule->setUniqueId($data);
                break;
            case 'source_family':
                $this->setSourceFamily($rule, $data);
                break;
            case 'destination_family':
                $this->setDestinationFamily($rule, $data);
                break;
        }
    }

    protected function setSourceFamily(Rule $rule, string $identifier): void
    {
        $family = $this->familyRepository->findOneByIdentifier($identifier);

        if (null === $family) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'source_family',
                'source family',
                'The source family does not exist',
                static::class,
                $identifier
            );
        }

        $rule->setSourceFamily($family);
    }

    protected function setDestinationFamily(Rule $rule, string $identifier): void
    {
        $family = $this->familyRepository->findOneByIdentifier($identifier);

        if (null === $family) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'destination_family',
                'destination family',
                'The destination family does not exist',
                static::class,
                $identifier
            );
        }

        $rule->setDestinationFamily($family);
    }
}
