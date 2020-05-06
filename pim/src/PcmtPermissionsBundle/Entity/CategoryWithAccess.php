<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CategoryWithAccess implements \Akeneo\Tool\Component\Classification\Model\CategoryInterface
{
    /** @var CategoryInterface */
    private $category;

    /** @var ArrayCollection */
    private $accesses;

    public function __construct(CategoryInterface $category)
    {
        $this->category = $category;
        $this->accesses = new ArrayCollection();
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
        return $this->category->getCode();
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
    public function setParent(?\Akeneo\Tool\Component\Classification\Model\CategoryInterface $parent = null)
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
    public function addChild(\Akeneo\Tool\Component\Classification\Model\CategoryInterface $child)
    {
        return $this->category->addChild($child);
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(\Akeneo\Tool\Component\Classification\Model\CategoryInterface $child)
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
}