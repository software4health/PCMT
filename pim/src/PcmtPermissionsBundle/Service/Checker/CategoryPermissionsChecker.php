<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Service\Checker;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryPermissionsChecker implements CategoryPermissionsCheckerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CategoryAccessRepositoryInterface */
    private $accessRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepositoryInterface $accessRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessRepository = $accessRepository;
    }

    public function hasAccessToProduct(string $type, CategoryAwareInterface $entity, ?UserInterface $user = null): bool
    {
        /* product without category has always access issue #438 */
        if (!$entity->getCategories()) {
            return true;
        }
        foreach ($entity->getCategories()->getIterator() as $category) {
            if ($this->isGranted($type, $category, $user)) {
                return true;
            }
        }

        return false;
    }

    private function isGranted(string $type, CategoryInterface $category, ?UserInterface $user = null): bool
    {
        $categoryWithAccess = $this->accessRepository->getCategoryWithAccess($category);

        /* category without set permissions has always access issue #438 */
        if (0 === $categoryWithAccess->getAccesses()->count()) {
            return true;
        }
        $user = $user ?? $this->tokenStorage->getToken()->getUser();

        /** @var Group $group */
        foreach ($user->getGroups() as $group) {
            /** @var CategoryAccess $access */
            foreach ($categoryWithAccess->getAccesses()->getIterator() as $access) {
                if ($group->getId() === $access->getUserGroup()->getId()) {
                    if (in_array($access->getLevel(), $this->getAccessLevels($type))) {
                        return true;
                    }
                    $categoryWithAccess->removeAccess($access);
                }
            }
        }

        return false;
    }

    private function getAccessLevels(string $type): array
    {
        switch ($type) {
            case CategoryAccess::VIEW_LEVEL:
                return [
                    CategoryAccess::VIEW_LEVEL,
                    CategoryAccess::EDIT_LEVEL,
                    CategoryAccess::OWN_LEVEL,
                ];
            case CategoryAccess::EDIT_LEVEL:
                return [
                    CategoryAccess::EDIT_LEVEL,
                    CategoryAccess::OWN_LEVEL,
                ];
            case CategoryAccess::OWN_LEVEL:
                return [
                    CategoryAccess::OWN_LEVEL,
                ];
            default:
                throw new ParameterNotFoundException($type);
        }
    }
}