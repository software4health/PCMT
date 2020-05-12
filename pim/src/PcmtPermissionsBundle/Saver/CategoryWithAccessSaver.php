<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;

class CategoryWithAccessSaver implements SaverInterface
{
    /** @var SaverInterface */
    private $categorySaver;

    /** @var SaverInterface */
    private $categoryAccessSaver;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    public function __construct(
        SaverInterface $categorySaver,
        SaverInterface $categoryAccessSaver,
        EntityManagerInterface $entityManager,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->categorySaver = $categorySaver;
        $this->categoryAccessSaver = $categoryAccessSaver;
        $this->entityManager = $entityManager;
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = []): void
    {
        /** @var CategoryWithAccess $object */
        $accesses = $this->categoryAccessRepository->findBy(['category' => $object->getCategory()]);
        foreach ($accesses as $access) {
            $this->entityManager->remove($access);
        }
        $this->entityManager->flush();

        $this->categorySaver->save($object->getCategory());
        foreach ($object->getAccesses() as $access) {
            $this->categoryAccessSaver->save($access);
        }
    }
}