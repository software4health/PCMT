<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\Builder;

use PcmtCoreBundle\Service\Builder\PaginatedResponseBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PaginatedResponseBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function buildPaginatedResponseDataProvider(): array
    {
        $input = [];
        $output = [];
        $numberOfElements = (2 * PaginatedResponseBuilder::PER_PAGE) + 1;
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

    /**
     * @dataProvider buildPaginatedResponseDataProvider
     */
    public function testBuildPaginatedResponse(array $input, array $result): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();
        $total = count($input);
        $response = $responseBuilder->buildPaginatedResponse($input, $total, PaginatedResponseBuilder::FIRST_PAGE);

        $this->assertJsonStringEqualsJsonString(json_encode(new JsonResponse($result)), json_encode($response));
    }

    public function testShouldThrowExceptionWhenInvalidNormalizerProvided(): void
    {
        $responseBuilder = $this->getResponseBuilderInstance();
        $invalidNormalizer = [$this->createMock(Response::class)];
        $this->expectException(\InvalidArgumentException::class);
        $responseBuilder->addNormalizers($invalidNormalizer);
    }

    private function getResponseBuilderInstance(): PaginatedResponseBuilder
    {
        return new PaginatedResponseBuilder([]);
    }
}