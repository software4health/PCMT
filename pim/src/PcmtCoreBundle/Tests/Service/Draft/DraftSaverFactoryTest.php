<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\Draft;

use PcmtCoreBundle\Entity\ProductDraftInterface;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;
use PcmtCoreBundle\Saver\ProductDraftSaver;
use PcmtCoreBundle\Saver\ProductModelDraftSaver;
use PcmtCoreBundle\Service\Draft\DraftSaverFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftSaverFactoryTest extends TestCase
{
    /** @var DraftSaverFactory */
    private $factory;

    /** @var ProductDraftSaver|MockObject */
    private $productDraftSaver;

    /** @var ProductModelDraftSaver|MockObject */
    private $productModelDraftSaver;

    protected function setUp(): void
    {
        $this->productDraftSaver = $this->createMock(ProductDraftSaver::class);
        $this->productModelDraftSaver = $this->createMock(ProductModelDraftSaver::class);
        $this->factory = new DraftSaverFactory($this->productDraftSaver, $this->productModelDraftSaver);
    }

    public function draftsProvider(): array
    {
        return [
            'product_draft'       => [
                'class'    => ProductDraftInterface::class,
                'expected' => ProductDraftSaver::class,
            ],
            'product_model_draft' => [
                'class'    => ProductModelDraftInterface::class,
                'expected' => ProductModelDraftSaver::class,
            ],
        ];
    }

    /**
     * @dataProvider draftsProvider
     *
     * @throws \ReflectionException
     */
    public function testFactoryWhenPassedDraftInstanceThenShouldReturnSpecifiedDraftSaver(
        string $class,
        string $expected
    ): void {
        $productDraft = $this->createMock($class);

        $this->assertInstanceOf($expected, $this->factory->create($productDraft));
    }
}