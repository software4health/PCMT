<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Updater;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;

class CategoryChildrenPermissionsUpdater
{
    /** @var CategoryAccessRepository */
    private $accessRepository;

    /** @var ArrayCollection */
    private $accesses;

    /** @var SaverInterface */
    private $categoryWithAccessSaver;

    public function __construct(CategoryAccessRepository $accessRepository, SaverInterface $categoryWithAccessSaver)
    {
        $this->accessRepository = $accessRepository;
        $this->categoryWithAccessSaver = $categoryWithAccessSaver;
    }

    public function update(CategoryWithAccess $categoryWithAccess): void
    {
        $this->accesses = $categoryWithAccess->getAccesses();
        $children = $categoryWithAccess->getChildren();
        foreach ($children as $child) {
            $this->updateCategory($child);
        }
    }

    private function updateCategory(CategoryInterface $category): void
    {
        $accesses = $this->accessRepository->findBy([
            'category' => $category,
        ]);

        if ($this->areAccessesDifferent($accesses)) {
            $categoryWithAccess = new CategoryWithAccess($category);
            foreach ($this->accesses as $access) {
                $newAccess = new CategoryAccess($category, $access->getUserGroup(), $access->getLevel());
                $categoryWithAccess->addAccess($newAccess);
            }
            $this->categoryWithAccessSaver->save($categoryWithAccess);
        }
        $children = $category->getChildren();
        foreach ($children as $child) {
            $this->updateCategory($child);
        }
    }

    private function areAccessesDifferent(iterable $accesses): bool
    {
        if ($this->getHashForAccesses($accesses) !== $this->getHashForAccesses($this->accesses)) {
            return true;
        }

        return false;
    }

    private function getHashForAccesses(iterable $accesses): string
    {
        $accessesNormalized = [];
        foreach ($accesses as $access) {
            /** @var CategoryAccess $access */
            $accessesNormalized[] = $access->getUserGroup()->getId() . '-' . $access->getLevel();
        }
        sort($accessesNormalized);

        return md5(json_encode($accessesNormalized));
    }
}