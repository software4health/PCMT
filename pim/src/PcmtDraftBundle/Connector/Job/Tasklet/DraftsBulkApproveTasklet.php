<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Tasklet;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\DraftRepositoryInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftsBulkApproveTasklet implements TaskletInterface
{
    /** @var UserRepository */
    private $userRepository;

    /** @var DraftFacade */
    private $draftFacade;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var DraftRepositoryInterface */
    protected $draftRepository;

    public function __construct(
        DraftFacade $draftFacade,
        NormalizerInterface $constraintViolationNormalizer,
        UserRepository $userRepository,
        DraftRepositoryInterface $draftRepository
    ) {
        $this->draftFacade = $draftFacade;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->userRepository = $userRepository;
        $this->draftRepository = $draftRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }
        $jobInstance = $this->stepExecution->getJobParameters();
        $chosenDrafts = [
            'allSelected' => $jobInstance->get('allSelected'),
            'excluded'    => $jobInstance->get('excluded'),
            'selected'    => $jobInstance->get('selected'),
        ];

        if ((bool) $chosenDrafts['allSelected']) {
            $drafts = $this->draftRepository->findBy(['status' => AbstractDraft::STATUS_NEW]);

            foreach ($drafts as $index => $draft) {
                if (in_array($draft->getId(), $chosenDrafts['excluded'])) {
                    unset($drafts[$index]);
                }
            }

            $draftsToApprove = $drafts;
        } else {
            $draftsToApprove = $this->draftRepository->findBy(
                [
                    'status' => AbstractDraft::STATUS_NEW,
                    'id'     => $chosenDrafts['selected'],
                ]
            );
        }

        $normalizedViolations = [];
        foreach ($draftsToApprove as $draft) {
            try {
                /** @var DraftInterface $draft */
                $user = $this->userRepository->findOneByIdentifier('admin');
                $this->draftFacade->approveDraft($draft, $user);
                $this->stepExecution->incrementSummaryInfo('approved');
            } catch (DraftViolationException $e) {
                $this->stepExecution->incrementSummaryInfo('failed');
                $context = $e->getContextForNormalizer();
                foreach ($e->getViolations() as $violation) {
                    $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                        $violation,
                        'internal_api',
                        $context
                    );
                }
            }
        }
    }
}