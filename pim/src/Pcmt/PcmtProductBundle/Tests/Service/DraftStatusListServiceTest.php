<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\UserManagement\Component\Model\User;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Normalizer\AttributeChangeNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftStatusNormalizer;
use Pcmt\PcmtProductBundle\Service\DraftStatusListService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * To run, type:
 * phpunit src/Pcmt/PcmtProductBundle/Tests/
 * in docker console
 *
 */
class DraftStatusListServiceTest extends TestCase
{
    /**
     * @var DraftStatusListService
     */
    private $draftStatusListService;

    public function setUp(): void
    {
        $this->draftStatusListService = new DraftStatusListService();
        parent::setUp();
    }

    public function testGetAll(): void
    {
        $list = $this->draftStatusListService->getAll();
        $this->assertIsArray($list);
        $this->assertGreaterThan(2, count($list));
        $this->assertIsInt(reset($list));
    }
}