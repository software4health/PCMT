<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Repository\RuleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function __construct(
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        NormalizerInterface $constraintViolationNormalizer,
        RuleRepository $ruleRepository
    ) {
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->ruleRepository = $ruleRepository;
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
     * @throws NotFoundHttpException
     */
    protected function getRuleOr404(string $identifier): Rule
    {
        $rule = $this->ruleRepository->findOneByIdentifier($identifier);
        if (null === $rule) {
            throw new NotFoundHttpException(
                sprintf('Rule with identifier "%s" not found', $identifier)
            );
        }

        return $rule;
    }

    /**
     * @AclAncestor("pcmt_permission_rules_edit")
     */
    public function postAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $rule = $this->getRuleOr404($identifier);

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
}