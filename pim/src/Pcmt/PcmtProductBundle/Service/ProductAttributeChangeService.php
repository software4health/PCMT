<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pcmt\PcmtProductBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\SerializerInterface;

class ProductAttributeChangeService
{
    /** @var AttributeChange[] */
    private $changes = [];

    /** @var SerializerInterface */
    private $versioningSerializer;

    public function __construct(SerializerInterface $versioningSerializer)
    {
        $this->versioningSerializer = $versioningSerializer;
    }

    public function get(?ProductInterface $newProduct, ?ProductInterface $previousProduct): array
    {
        $this->changes = [];

        if (!$newProduct) {
            return $this->changes;
        }

        $newValues = $this->versioningSerializer->normalize($newProduct, 'flat');
        $previousValues = $previousProduct ?
            $this->versioningSerializer->normalize($previousProduct, 'flat') :
            [];

        foreach ($newValues as $attribute => $newValue) {
            $previousValue = $previousValues[$attribute] ?? null;
            $this->createChange($attribute, $newValue, $previousValue);
        }

        return $this->changes;
    }

    /**
     * @param string $attribute
     */
    private function createChange($attribute, $value, $previousValue): void
    {
        $value = $value ?? null;
        $previousValue = $previousValue ?? null;

        if ($value === $previousValue) {
            return;
        }
        $this->changes[] = new AttributeChange($attribute, (string) $previousValue, (string) $value);
    }
}