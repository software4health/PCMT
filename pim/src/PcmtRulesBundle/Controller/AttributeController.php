<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Controller;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeController
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var NormalizerInterface */
    private $lightAttributeNormalizer;

    /** @var UserContext */
    private $userContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $lightAttributeNormalizer,
        UserContext $userContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->lightAttributeNormalizer = $lightAttributeNormalizer;
        $this->userContext = $userContext;
    }

    public function getForFamiliesAction(Request $request): Response
    {
        $sourceFamilyCode = $request->query->get('sourceFamily');
        $destinationFamilyCode = $request->query->get('destinationFamily');
        $sourceFamily = $sourceFamilyCode ? $this->familyRepository->findOneByIdentifier($sourceFamilyCode) : null;
        $destinationFamily = $destinationFamilyCode ? $this->familyRepository->findOneByIdentifier($destinationFamilyCode) : null;
        if (!$sourceFamily || !$destinationFamily) {
            return new JsonResponse([]);
        }

        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes = array_intersect($attributes1, $attributes2);

        $normalizedAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $attributes);

        return new JsonResponse($normalizedAttributes);
    }
}