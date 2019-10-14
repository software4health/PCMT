<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtAttributeBundle\Event\ProductFetchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UpdateProductValueIfHasConcatenatedAttribute implements EventSubscriberInterface
{
    private const IS_MISSING = 'MISSING';
    private const IS_EMPTY = 'EMPTY';

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /** @var FamilyRepositoryInterface $concatenatedAttributeRepository */
    private $concatenatedAttributeRepository;

    /** @var AttributeRepositoryInterface $attributeRepository */
    protected $attributeRepository;

    /** @var ObjectUpdaterInterface $productValuesUpdater */
    protected $productValuesUpdater;

    /** @var SaverInterface $productSaver */
    protected $productSaver;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        FamilyRepositoryInterface $concatenatedAttributeRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productValuesUpdater,
        SaverInterface $productSaver
    )
    {
        $this->productRepository = $productRepository;
        $this->concatenatedAttributeRepository = $concatenatedAttributeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productValuesUpdater = $productValuesUpdater;
        $this->productSaver = $productSaver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
          ProductFetchEvent::class => [
              ['onProductFetch', 10]
          ]
        ];
    }

    public function onProductFetch(ProductFetchEvent $event): void
    {
        $product = $this->productRepository->find(
            $event->getProductId()
        );

        $family = $product->getFamily();
        $concatenatedAttributes = $this->concatenatedAttributeRepository->getConcatenatedAttributes($family);

        $values = [];
        foreach ($concatenatedAttributes as $counter => $concatenatedAttribute){

            $attributeName = $concatenatedAttribute['code'];
            $memberAttributes = $this->attributeRepository->findBy(['code' =>
                explode(',', $concatenatedAttribute['properties']['attributes'])
            ]);

            $separator =  $concatenatedAttribute['properties']['separators'];
            $concatenatedValue = [];

            foreach ($memberAttributes as $memberAttribute){

                if($product->hasAttribute($memberAttribute->getCode())){

                    $value = $product->getValue($memberAttribute->getCode());
                    if(!(null == $value || '' == $value || [] == $value)){
                        $concatenatedValue[] = $value->__toString();
                    }else {
                        $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_EMPTY;
                    }
                } else {
                    $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_MISSING;
                }
            }

            $values[$attributeName]['data']['data'] = [implode($separator,$concatenatedValue)];
            $values[$attributeName]['data']['locale'] = null;
            $values[$attributeName]['data']['scope'] = null;

            $this->productValuesUpdater->update($product, $values);
            $this->productSaver->save($product);
        }
    }

}