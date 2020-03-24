<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\DataFixtures;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;

class NewDraftFixture implements FixtureInterface
{
    public function load(
        ObjectManager $manager
    ): void {
        $draftBuilder = new NewProductDraftBuilder();
        $userRepository = $manager->getRepository(User::class);

        $user = $userRepository->findOneBy([
            'id' => 1,
        ]);

        $manager->persist($user);
        $manager->flush();

        $draft = $draftBuilder
            ->withOwner($user)
            ->withId(1)
            ->withProductData([
                'identifier' => $this->generateTestIdentifier(),
                'family'     => 'MD_HUB',
            ])
            ->build();

        $manager->persist($draft);
        $manager->flush();
    }

    private function generateTestIdentifier(): string
    {
        /*
         * @todo - generate test data in other way
         */
        return 'behat_unique_id_' . random_int(1, 100000);
    }
}