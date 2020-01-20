<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

class PcmtImportViaDraftStep extends ItemStep
{
    /** @var UserRepository */
    private $userRepository;

    public function setUserRepository(UserRepositoryInterface $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution): void
    {
        $jobExecution = $stepExecution->getJobExecution();
        $user = $this->userRepository->findOneByIdentifier($jobExecution->getUser());
        $this->writer->setUser($user);
        parent::doExecute($stepExecution);
    }
}