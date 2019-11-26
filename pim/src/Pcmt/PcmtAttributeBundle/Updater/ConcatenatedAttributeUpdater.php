<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Updater;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;

class ConcatenatedAttributeUpdater extends AttributeUpdater
{
    /** @var ConcatenatedAttributeFieldsUpdater */
    protected $attributeFieldsUpdater;

    public function __construct(
        AttributeGroupRepositoryInterface $attrGroupRepo,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $registry,
        TranslatableUpdater $translatableUpdater,
        array $properties,
        ConcatenatedAttributeFieldsUpdater $attributeFieldsUpdater
    ) {
        $this->attributeFieldsUpdater = $attributeFieldsUpdater;
        parent::__construct($attrGroupRepo, $localeRepository, $registry, $translatableUpdater, $properties);
    }

    /**
     * {@inheritdoc}
     */
    protected function setData(AttributeInterface $attribute, $field, $data): void
    {
        if ($attribute instanceof ConcatenatedAttributeWriteModel) {
            switch ($field) {
                case 'separators':
                    $this->concatenatedAttributeUpdater->updateSeparators($attribute, $data);

                    break;
                case 'attributes':
                    $this->concatenatedAttributeUpdater->updateAttributes($attribute, $data);

                    break;
            }
        }
        parent::setData($attribute, $field, $data);
    }
}