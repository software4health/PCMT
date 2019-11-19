<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\UserManagement\Component\Model\User;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Normalizer\AttributeChangeNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftStatusNormalizer;
use Pcmt\PcmtProductBundle\Service\DraftStatusListService;
use Pcmt\PcmtProductBundle\Service\DraftStatusTranslatorService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * To run, type:
 * phpunit src/Pcmt/PcmtProductBundle/Tests/
 * in docker console
 *
 */
class DraftStatusTranslatorServiceTest extends TestCase
{
    /**
     * @var DraftStatusTranslatorService
     */
    private $draftStatusTranslatorService;

    /**
     * @var string
     */
    private $nameTranslated = 'name translated';

    public function setUp(): void
    {
        $translatorService = $this->createMock(TranslatorInterface::class);
        $translatorService->method('trans')->willReturn($this->nameTranslated);
        $this->draftStatusTranslatorService = new DraftStatusTranslatorService($translatorService);
        parent::setUp();
    }

    public function testGetName(): void
    {
        $name = $this->draftStatusTranslatorService->getName(AbstractProductDraft::STATUS_NEW);
        $this->assertNotEmpty($name);
        $this->assertIsString($name);
    }

    public function testGetNameTranslated(): void
    {
        $name = $this->draftStatusTranslatorService->getNameTranslated(AbstractProductDraft::STATUS_NEW);
        $this->assertEquals($this->nameTranslated, $name);
    }
}