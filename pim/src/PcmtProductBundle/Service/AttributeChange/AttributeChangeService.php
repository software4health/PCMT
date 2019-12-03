<?php

declare(strict_types=1);

namespace PcmtProductBundle\Service\AttributeChange;

use PcmtProductBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeChangeService
{
    /** @var AttributeChange[] */
    protected $changes = [];

    /** @var SerializerInterface */
    protected $versioningSerializer;

    public function __construct(SerializerInterface $versioningSerializer)
    {
        $this->versioningSerializer = $versioningSerializer;
    }

    /**
     * @param array|object|string|int|null $value
     * @param array|object|string|int|null $previousValue
     */
    protected function createChange(string $attribute, $value, $previousValue): void
    {
        $value = $value ?: null;
        $previousValue = $previousValue ?: null;

        if ($value === $previousValue) {
            return;
        }
        $this->changes[] = new AttributeChange($attribute, (string) $previousValue, (string) $value);
    }
}