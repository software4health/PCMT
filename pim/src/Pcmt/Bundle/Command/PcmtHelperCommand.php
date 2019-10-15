<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    protected static $defaultName = 'pcmt:command';

    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $attrRepo = $this->getContainer()->get('pim_catalog.repository.attribute');
        $productRepo = $this->getContainer()->get('pim_catalog.repository.product');
        $productUpdater = $this->getContainer()->get('pim_catalog.updater.product');
        $productSaver = $this->getContainer()->get('pim_catalog.saver.product');
        $productValidator = $this->getContainer()->get('pim_catalog.validator.product');
       // $productValuesUpdater = $this->getContainer()->get('pim_catalog.updater.entity_with_values');
        $productValuesUpdater = $this->getContainer()->get('pcmt_catalog.updater.product_value_concatenated');

        /**
         * test - fetch product
         */
        //1. find product and family
        $product = $productRepo->find('1207');
        $family = $product->getFamily();
        //fetch concatenated attributes
        $concatenatedRepository = $this->getContainer()->get('pcmt_catalog.repository.pcmt_family_concatenated_attribute');
        $concatenatedAttributes = $concatenatedRepository->getConcatenatedAttributes($family);
        //dump($concatenatedAttributes);
        $productValues = $product->getRawValues();
        $values = []; //input structure to values updater;

        foreach ($concatenatedAttributes as $counter => $concatenatedAttribute){
            $attributeName = $concatenatedAttribute['code'];
            $memberAttributes = $attrRepo->findBy(['id' =>
                explode(',', $concatenatedAttribute['properties']['attributes'])
            ]);//fetch member attributes from concatenated attributes:
            $separator =  $concatenatedAttribute['properties']['separators']; //separator

            $concatenatedValue = [];
            foreach ($memberAttributes as $memberAttribute){

                //if not, then check if any of the product values match member attribute key
                if($product->hasAttribute($memberAttribute->getCode())){
                    //if product has these properties, then extract values from them (if empty, mark empty) construct values array
                    $value = $product->getValue($memberAttribute->getCode());
                    $concatenatedValue[] = $value->__toString();
                }
            }

            $values[$attributeName]['data']['data'] = [implode($separator,$concatenatedValue)];
            $values[$attributeName]['data']['locale'] = null;
            $values[$attributeName]['data']['scope'] = null;


            //update the product value:
            $productValuesUpdater->update($product, $values);

            $productSaver->save($product);
        }
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}