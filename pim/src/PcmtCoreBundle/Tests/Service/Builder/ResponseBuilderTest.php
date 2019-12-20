<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\Builder;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Service\Builder\ResponseBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function buildPaginatedResponseDataProvider(): array
    {
        $input = [];
        $output = [];
        $numberOfElements = (2 * ResponseBuilder::PER_PAGE) + 1;
        for ($i = 0; $i < $numberOfElements; $i++) {
            $input[$i] = 'value' . $i;
            $output[$i] = 'value' . $i;
        }
        $output['params']['total'] = $numberOfElements;
        $output['params']['firstPage'] = 1;
        $output['params']['currentPage'] = 1;
        $output['params']['lastPage'] = 3;

        return [
            [
                $input,
                $output,
            ],
        ];
    }

    public function testBuildEmptyResponse(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();

        $response = $responseBuilder->build();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(json_encode(new \stdClass()), $response->getContent());
    }

    public function testBuildEmptyResponseWithNormalizer(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();

        $normalizer = $this->createMock(NormalizerInterface::class);

        $response = $responseBuilder
            ->addNormalizers([$normalizer])
            ->build();

        $this->assertSame(json_encode(new \stdClass()), $response->getContent());
    }

    public function testBuildResponseForDraftWithNormalizer(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();

        $normalizer = $this->createMock(NormalizerInterface::class);
        $draft = $this->createMock(Attribute::class);
        $normalizerResult = [
            'id' => 1234,
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with([$draft])
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->addNormalizers([$normalizer])
            ->setData([$draft])
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    public function testBuildResponseForDraftWithNormalizerAndIncludeProductInContext(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();

        $normalizer = $this->createMock(NormalizerInterface::class);
        $draft = $this->createMock(Attribute::class);
        $normalizerResult = [
            'id'      => 1234,
            'product' => [
                'id' => 4321,
            ],
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with([$draft], null, ['include_product' => true])
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->addNormalizers([$normalizer])
            ->setData([$draft])
            ->setContext(['include_product' => true])
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    public function testBuildResponseForDraftsListWithNormalizer(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();

        $normalizer = $this->createMock(NormalizerInterface::class);
        $draft1 = $this->createMock(Attribute::class);
        $draft2 = $this->createMock(Attribute::class);
        $normalizerResult = [
            ['id' => 1234],
            ['id' => 1235],
        ];

        $normalizer
            ->method('supportsNormalization')
            ->willReturn(true);

        $normalizer
            ->method('normalize')
            ->with(
                [
                    $draft1,
                    $draft2,
                ]
            )
            ->willReturn($normalizerResult);

        $response = $responseBuilder
            ->addNormalizers([$normalizer])
            ->setData(
                [
                    $draft1,
                    $draft2,
                ]
            )
            ->build();

        $this->assertSame(json_encode($normalizerResult), $response->getContent());
    }

    /**
     * @dataProvider buildPaginatedResponseDataProvider
     */
    public function testBuildPaginatedResponse(array $input, array $result): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();
        $total = count($input);
        $response = $responseBuilder->buildPaginatedResponse($input, $total, ResponseBuilder::FIRST_PAGE);

        $this->assertJsonStringEqualsJsonString(json_encode(new JsonResponse($result)), json_encode($response));
    }

    public function testShouldThrowExceptionWhenInvalidNormalizerProvided(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();
        $invalidNormalizer = [$this->createMock(Response::class)];
        $this->expectException(\InvalidArgumentException::class);
        $responseBuilder->addNormalizers($invalidNormalizer);
    }

    private function getResponseBuilderInstance(): ResponseBuilder
    {
        return new ResponseBuilder([]);
    }
}