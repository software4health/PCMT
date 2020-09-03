<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Controller;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Repository\RuleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RuleController
{
    /** @var RuleRepository */
    protected $ruleRepository;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $saver;

    /** @var NormalizerInterface */
    private $constraintViolationNormalizer;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        NormalizerInterface $constraintViolationNormalizer,
        RuleRepository $ruleRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->ruleRepository = $ruleRepository;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * @AclAncestor("pcmt_permission_rules_view")
     */
    public function getAction(Rule $rule): Response
    {
        return new JsonResponse($this->normalizer->normalize(
            $rule,
            'internal_api'
        ));
    }

    /**
     * @AclAncestor("pcmt_permission_rules_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        $rule = new Rule();
        $this->updater->update($rule, (array) $data);
        $violations = $this->validator->validate($rule);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['group' => $rule]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->saver->save($rule);

        return new JsonResponse($this->normalizer->normalize(
            $rule,
            'internal_api'
        ));
    }

    /**
     * @AclAncestor("pcmt_permission_rules_edit")
     */
    public function postAction(Rule $rule, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        $this->updater->update($rule, $data);

        $violations = $this->validator->validate($rule);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($rule);

        return new JsonResponse(
            $this->normalizer->normalize(
                $rule,
                'internal_api'
            )
        );
    }

    /**
     * @AclAncestor("pcmt_permission_rules_run")
     */
    public function runAction(Rule $rule): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new \Exception('User not found');
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('pcmt_rule_process');

        if (!$jobInstance) {
            throw new \Exception('Job instance not found.');
        }

        $configuration = $jobInstance->getRawParameters();
        $configuration['ruleId'] = $rule->getId();

        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        try {
        } catch (\Throwable $e) {
            return new JsonResponse([
                'successful' => false,
                'message'    => 'pcmt.rules.flash.running_start_error',
            ]);
        }

        return new JsonResponse([
            'successful' => true,
            'message'    => 'pcmt.rules.flash.running_started',
        ]);
    }
}