<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Service;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccessInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryPermissionsChecker implements CategoryPermissionsCheckerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function hasAccessToProduct(string $type, ?CategoryAwareInterface $entity, ?UserInterface $user = null): bool
    {
        /* product without category has always access issue #438 */
        if (null === $entity || 0 === $entity->getCategories()->count()) {
            return true;
        }
        foreach ($entity->getCategories()->getIterator() as $category) {
            if ($this->isGranted($type, $category, $user)) {
                return true;
            }
        }

        return false;
    }

    public function isGranted(string $type, CategoryWithAccessInterface $category, ?UserInterface $user = null): bool
    {
        /* category without set permissions has always access issue #438 */
        if (0 === $category->getAccesses()->count()) {
            return true;
        }
        $user = $user ?? $this->tokenStorage->getToken()->getUser();

        /** @var Group $group */
        foreach ($user->getGroups() as $group) {
            /** @var CategoryAccess $access */
            foreach ($category->getAccesses()->getIterator() as $access) {
                if ($group->getId() === $access->getUserGroup()->getId()) {
                    if (in_array($access->getLevel(), $this->getAccessLevels($type))) {
                        return true;
                    }
//                    $category->removeAccess($access);
                }
            }
        }

        return false;
    }

    private function getAccessLevels(string $type): array
    {
        switch ($type) {
            case CategoryPermissionsCheckerInterface::VIEW_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
            case CategoryPermissionsCheckerInterface::EDIT_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
            case CategoryPermissionsCheckerInterface::OWN_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
            default:
                throw new ParameterNotFoundException($type);
        }
    }
}