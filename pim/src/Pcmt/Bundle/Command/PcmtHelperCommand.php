<?php

declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtAttributeBundle\Entity\Attribute;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Value\ConcatenatedAttributeValue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    /** @var string */
    protected static $defaultName = 'pcmt:command';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $productRepo = $this->getContainer()->get('pim_catalog.repository.product');
        $attributeRepo = $this->getContainer()->get('pim_catalog.repository.attribute');
        $familyRepo = $this->getContainer()->get('pim_catalog.repository.family');
        $concatenatedAttributesUpdater = $this->getContainer()->get(
            'pcmt_catalog.product.updater.concatenated_attributes_for_product_and_model'
        );
        $em = $this->getEntityManager();

        $product = $productRepo->find(2162);

        foreach ($product->getValues() as $attributeValue) {
            if (ConcatenatedAttributeValue::class === get_class($attributeValue)) {
                $concatenatedAttribute = $attributeRepo->findOneBy(
                    [
                        'code' => $attributeValue->getAttributeCode(),
                    ]
                );
                $memberAttributes = $attributeRepo->findBy(
                    [
                        'code' => explode(',', $concatenatedAttribute->getProperty('attributes')),
                    ]
                );
                $concatenatedAttributesUpdater->update(
                    $product,
                    [
                        'concatenatedAttribute' => $concatenatedAttribute,
                        'memberAttributes'      => $memberAttributes,
                    ]
                );
            }
        }

        $family = $familyRepo->find(4);
        $cncAttribute = new Attribute();
        $cncAttribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $family->addAttribute($cncAttribute);

        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}