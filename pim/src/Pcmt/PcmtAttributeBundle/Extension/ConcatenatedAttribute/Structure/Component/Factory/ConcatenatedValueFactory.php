<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\AbstractValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Pcmt\PcmtAttributeBundle\Entity\PcmtFamilyRepository;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Factory\Exception\ConcatenatedAttributeException;

class ConcatenatedValueFactory extends AbstractValueFactory
{
    /** @var AttributeRepositoryInterface $attributesRepository */
    protected $attributesRepository;

    /** @var PcmtFamilyRepository $concatenatedAttributeRepository */
    protected $concatenatedAttributeRepository;

    /** @var ValueFactory $valueFactory */
    protected $valueFactory;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ValueFactory $valueFactory,
        string $productValueClass,
        string $supportedAttributeType
    )
    {
        parent::__construct($productValueClass, $supportedAttributeType);
        $this->attributesRepository = $attributeRepository;
        $this->valueFactory = $valueFactory;
    }

    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData): ?string
    {
        /**
         * here now data is in format of array wth single value :
         * array:1 [
        *     0 => "594877:13.00 EUR"
        * ]
         * possibly it can be array with elements: [ 0 => attribute1, 1 => separator1, 2 => attribute 2]
         */
        if(is_array($data)){
            return $data[0];
        }

        return $data;
    }
}