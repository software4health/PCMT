<?php

declare(strict_types=1);

namespace PcmtProductBundle\Provider\Form;

use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Pcmt\PcmtProductBundle\Entity\DraftInterface;

class DraftFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($product): string
    {
        return 'pcmt-product-drafts-edit';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof DraftInterface;
    }
}