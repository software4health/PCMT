<?php

declare(strict_types=1);

namespace PcmtProductBundle\Tests\Service\AttributeChange;

use PcmtProductBundle\Service\AttributeChange\AttributeChangeService;

class FakeAttributeChangeService extends AttributeChangeService
{
    /**
     * {@inheritdoc}
     */
    public function createChange(string $attribute, $value, $previousValue): void
    {
        parent::createChange($attribute, $value, $previousValue);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}