<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Form;

use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Model\ConcatenatedAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcatenatedAttributeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConcatenatedAttribute::class,
            'allow_extra_fields' => true
        ]);
    }
}