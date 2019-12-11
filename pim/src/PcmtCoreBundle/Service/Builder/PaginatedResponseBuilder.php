<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class PaginatedResponseBuilder
{
    public const FIRST_PAGE = 1;
    public const PER_PAGE = 2;

    /** @var NormalizerInterface[] */
    protected $normalizers = [];

    public function __construct(array $normalizers)
    {
        $this->addNormalizers($normalizers);
    }

    public function buildPaginatedResponse(array $result, int $total, ?int $page): JsonResponse
    {
        $response['objects'] = $result;
        $response['params']['total'] = $total;
        $response['params']['firstPage'] = self::FIRST_PAGE;
        $response['params']['currentPage'] = $page;
        $response['params']['lastPage'] = ceil(($total / self::PER_PAGE));
        $serializer = new Serializer(
            $this->normalizers
        );
        $data = $serializer->normalize($response);

        return new JsonResponse($data);
    }

    public function addNormalizers(array $normalizers): void
    {
        array_walk($normalizers, function (&$value): void {
            if (!$value instanceof NormalizerInterface) {
                throw new \InvalidArgumentException('Invalid normalizer class.');
            }
            $this->normalizers[] = $value;
        });
    }
}