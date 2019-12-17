<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ResponseBuilder
{
    public const FIRST_PAGE = 1;
    public const PER_PAGE = 25;

    /** @var bool */
    protected $isPaginated = false;

    /** @var int */
    protected $total = 0;

    /** @var int */
    protected $page = 1;

    /** @var mixed */
    protected $data = null;

    /** @var mixed[] */
    protected $context = [];

    /** @var NormalizerInterface[] */
    protected $normalizers = [];

    public function __construct(array $normalizers)
    {
        $this->addNormalizers($normalizers);
    }

    public function addNormalizers(array $normalizers): self
    {
        array_walk($normalizers, function (&$value): void {
            if (!$value instanceof NormalizerInterface) {
                throw new \InvalidArgumentException('Invalid normalizer class.');
            }
            $this->normalizers[] = $value;
        });

        return $this;
    }

    /**
     * @param int|float|bool|array|object|string $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setPagination(int $total, ?int $page): self
    {
        $this->isPaginated = true;
        $this->total = $total;
        $this->page = $page;

        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function build(): JsonResponse
    {
        if ($this->isPaginated) {
            $response = [
                'objects' => $this->data,
                'params'  => [
                    'total'       => $this->total,
                    'firstPage'   => self::FIRST_PAGE,
                    'currentPage' => $this->page,
                    'lastPage'    => ceil($this->total / self::PER_PAGE),
                ],
            ];
        } else {
            $response = $this->data;
        }

        $serializer = new Serializer($this->normalizers);

        return new JsonResponse($serializer->normalize($response, null, $this->context));
    }

    public function buildPaginatedResponse(array $result, int $total, ?int $page): JsonResponse
    {
        return $this
            ->setData($result)
            ->setPagination($total, $page)
            ->build();
    }
}