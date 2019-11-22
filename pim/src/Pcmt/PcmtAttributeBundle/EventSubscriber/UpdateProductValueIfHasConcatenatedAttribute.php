<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtAttributeBundle\Event\ProductFetchEvent;
use Pcmt\PcmtProductBundle\Event\ProductModelFetchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateProductValueIfHasConcatenatedAttribute implements EventSubscriberInterface
{
    private const IS_MISSING = 'MISSING';
    private const IS_EMPTY = 'EMPTY';

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ProductRepositoryInterface $productRepository */
    private $productRepository;

    /** @var ProductModelRepositoryInterface $productModelRepository */
    private $productModelRepository;

    /** @var FamilyVariantRepositoryInterface $familyVariantRepository */
    private $familyVariantRepository;

    /** @var FamilyRepositoryInterface $concatenatedAttributeRepository */
    private $concatenatedAttributeRepository;

    /** @var FamilyRepositoryInterface $familyRepository */
    private $familyRepository;

    /** @var AttributeRepositoryInterface $attributeRepository */
    private $attributeRepository;

    /** @var ObjectUpdaterInterface $productValuesUpdater */
    private $productValuesUpdater;

    /** @var ObjectUpdaterInterface $productModelValuesUpdater */
    private $productModelValuesUpdater;

    /** @var SaverInterface $productSaver */
    private $productSaver;

    /** @var SaverInterface $productModelSaver */
    private $productModelSaver;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        FamilyRepositoryInterface $concatenatedAttributeRepository,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productValuesUpdater,
        ObjectUpdaterInterface $productModelValuesUpdater,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver
    ) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->concatenatedAttributeRepository = $concatenatedAttributeRepository;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productValuesUpdater = $productValuesUpdater;
        $this->productModelValuesUpdater = $productModelValuesUpdater;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
          ProductFetchEvent::class => [
              ['onProductFetch', 10],
          ],
          ProductModelFetchEvent::class => [
              ['onProductModelFetch', 10],
          ],
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
        foreach ($concatenatedAttributes as $counter => $concatenatedAttribute) {
            $attributeName = $concatenatedAttribute['code'];
            $memberAttributes = $this->attributeRepository->findBy(['code' => explode(',', $concatenatedAttribute['properties']['attributes']),
            ]);

            $separator = $concatenatedAttribute['properties']['separators'];
            $concatenatedValue = [];

            foreach ($memberAttributes as $memberAttribute) {
                if ($product->hasAttribute($memberAttribute->getCode())) {
                    $value = $product->getValue($memberAttribute->getCode());
                    if (!(null == $value || '' == $value || [] == $value)) {
                        $concatenatedValue[] = $value->__toString();
                    } else {
                        $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_EMPTY;
                    }
                } else {
                    $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_MISSING;
                }
            }

            $values[$attributeName]['data']['data'] = [implode($separator, $concatenatedValue)];
            $values[$attributeName]['data']['locale'] = null;
            $values[$attributeName]['data']['scope'] = null;

            $this->productValuesUpdater->update($product, $values);
            $this->productSaver->save($product);
        }
    }

    public function onProductModelFetch(ProductModelFetchEvent $event): void
    {
        $productModel = $this->productModelRepository->find(
            $event->getProductModelId()
        );

        $familyVariant = $productModel->getFamilyVariant();
        $concatenatedAttributes = $this->concatenatedAttributeRepository->getConcatenatedAttributes(
            $familyVariant->getFamily()
        );

        $values = [];
        foreach ($concatenatedAttributes as $counter => $concatenatedAttribute) {
            $attributeName = $concatenatedAttribute['code'];
            $memberAttributes = $this->attributeRepository->findBy(['code' => explode(',', $concatenatedAttribute['properties']['attributes']),
            ]);

            $separator = $concatenatedAttribute['properties']['separators'];
            $concatenatedValue = [];

            foreach ($memberAttributes as $memberAttribute) {
                if ($productModel->hasAttribute($memberAttribute->getCode())) {
                    $value = $productModel->getValue($memberAttribute->getCode());
                    if (!(null == $value || '' == $value || [] == $value)) {
                        $concatenatedValue[] = $value->__toString();
                    } else {
                        $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_EMPTY;
                    }
                } else {
                    $concatenatedValue[] = $memberAttribute->getCode() . ' ' . self::IS_MISSING;
                }
            }

            $values[$attributeName]['data']['data'] = [implode($separator, $concatenatedValue)];
            $values[$attributeName]['data']['locale'] = null;
            $values[$attributeName]['data']['scope'] = null;

            $this->productValuesUpdater->update($productModel, $values);
            $this->productModelSaver->save($productModel);
        }
    }
}