<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\UserManagement\Component\Model\User;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * To run, type:
 * phpunit src/Pcmt/PcmtProductBundle/Tests/
 * in docker console
 *
 */
class DraftNormalizerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testNormalizeNewProductDraft()
    {
        $attribute1 = "attribute1";
        $productData = [
            $attribute1 => "NEW",
            "attribute2" => 123
        ];
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $created = new \DateTime();
        $draft = new NewProductDraft($productData, $author, $created, 0, 0);

        $normalizer = new DraftNormalizer();
        $array = $normalizer->normalize($draft);

        $this->assertNotEmpty($array["changes"]);
        $this->assertEquals(count($productData), count($array["changes"]));
        $this->assertEquals("Alfred Nobel", $array["author"]);
        $this->assertEquals($attribute1, $array["changes"][0]["attribute"]);
    }

    public function testNormalizePendingProductDraft()
    {
        $attribute = "attribute3";
        $productData = [
            $attribute => "NEW",
            "attribute4" => 123
        ];
        $author = new User();
        $created = new \DateTime();

        $product = $this->getMockBuilder(Product::class)->getMock();
        $identifier = 'Id23';
        $product->method('getIdentifier')->willReturn($identifier);

        $draft = new PendingProductDraft($product, $productData, $author, $created, 0, 0);

        $normalizer = new DraftNormalizer();
        $array = $normalizer->normalize($draft);

        $this->assertNotEmpty($array["changes"]);
        $this->assertEquals(count($productData), count($array["changes"]));
        $this->assertEquals($attribute, $array["changes"][0]["attribute"]);
        $this->assertEquals($identifier, $array["label"]);
    }

}