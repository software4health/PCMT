<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Provider\Field;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class ConcatenatedAttributeFieldProvider implements FieldProviderInterface
{
    protected $fields = [
        PcmtAtributeTypes::CONCATENATED_FIELDS => 'pcmt_concatenated_attribute_text_field'
    ];

    public function getField($attribute)
    {
        return $this->fields[$attribute->getType()];
    }

    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getType(), array_keys($this->fields));
    }
}