<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Entity;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\Collections\ArrayCollection;

class CategoryWithAccess implements CategoryInterface
{
    /** @var string */
    private $code;

    /** @var CategoryInterface */
    private $category;

    /** @var ArrayCollection */
    private $accesses;

    public function __construct(CategoryInterface $category)
    {
        $this->code = $category->getCode();
        $this->category = $category;
        $this->accesses = new ArrayCollection();
    }

    public function removeAccess(CategoryAccess $access): void
    {
        $this->accesses->removeElement($access);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory(): CategoryInterface
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function addAccess(CategoryAccess $access): void
    {
        $this->accesses->add($access);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->category->getTranslations();
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        $translation->setForeignKey($this->category);

        return $this->category->addTranslation($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->category->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this->category->setCode($code);
    }

    /**
     * {@inheritdoc}
     */
    public function setLeft($left)
    {
        return $this->category->setLeft($left);
    }

    /**
     * {@inheritdoc}
     */
    public function getLeft()
    {
        return $this->category->getLeft();
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel($level)
    {
        return $this->category->setLevel($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->category->getLevel();
    }

    /**
     * {@inheritdoc}
     */
    public function setRight($right)
    {
        return $this->category->setRight($right);
    }

    /**
     * {@inheritdoc}
     */
    public function getRight()
    {
        return $this->category->getRight();
    }

    /**
     * {@inheritdoc}
     */
    public function setRoot($root)
    {
        return $this->category->setRoot($root);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->category->getRoot();
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(?CategoryInterface $parent = null)
    {
        return $this->category->setParent($parent);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->category->getParent();
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return $this->category->isRoot();
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(CategoryInterface $child)
    {
        return $this->category->addChild($child);
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(CategoryInterface $child)
    {
        return $this->category->removeChild($child);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return $this->category->hasChildren();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->category->getChildren();
    }

    private function getAccessesOfLevel(string $level): array
    {
        $accesses = [];
        foreach ($this->accesses as $access) {
            /** @var CategoryAccess $access */
            if ($level === $access->getLevel()) {
                $accesses[] = $access->getUserGroup();
            }
        }

        return $accesses;
    }

    public function getViewAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryAccess::VIEW_LEVEL);
    }

    public function getEditAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryAccess::EDIT_LEVEL);
    }

    public function getOwnAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryAccess::OWN_LEVEL);
    }

    private function setAccessesByUserGroups(array $userGroups, string $level): void
    {
        foreach ($userGroups as $userGroup) {
            if (!$this->checkIfAccessExists($userGroup, $level)) {
                $categoryAccess = new CategoryAccess($this->getCategory(), $userGroup, $level);
                $this->accesses->add($categoryAccess);
            }
        }
    }

    public function setViewAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryAccess::VIEW_LEVEL);
    }

    public function setEditAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryAccess::EDIT_LEVEL);
    }

    public function setOwnAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryAccess::OWN_LEVEL);
    }

    public function checkIfAccessExists(Group $userGroup, string $level): bool
    {
        foreach ($this->accesses as $access) {
            /** @var CategoryAccess $access */
            if ($access->getUserGroup()->getId() === $userGroup->getId() && $level === $access->getLevel()) {
                return true;
            }
        }

        return false;
    }

    public function getAccesses(): ArrayCollection
    {
        return $this->accesses;
    }

    public function clearAccesses(): void
    {
        $this->accesses = new ArrayCollection();
    }
}