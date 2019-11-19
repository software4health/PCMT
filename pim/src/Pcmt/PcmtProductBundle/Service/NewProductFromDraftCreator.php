<?php

namespace Pcmt\PcmtProductBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Exception\DraftViolationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewProductFromDraftCreator
{
    /** @var NewProductDraft */
    private $draft;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var UserContext */
    private $userContext;

    /** @var FilterInterface */
    private $emptyValuesFilter;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        UserContext $userContext,
        FilterInterface $emptyValuesFilter,
        ObjectUpdaterInterface $productUpdater,
        AttributeFilterInterface $productAttributeFilter
    )
    {
        $this->productBuilder = $productBuilder;
        $this->validator = $validator;
        $this->productSaver = $productSaver;
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->userContext = $userContext;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productUpdater = $productUpdater;
        $this->productAttributeFilter = $productAttributeFilter;
    }

    public function create(NewProductDraft $draft): void
    {
        $this->draft = $draft;

        $data = $draft->getProductData();

        if (isset($data['parent'])) {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );

            if (isset($data['values'])) {
                $this->updateProduct($product, $data);
            }
        } else {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );
        }

        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);
        } else {
            throw new DraftViolationException($violations, $product);
        }
    }

    /**
     * Updates product with the provided data
     * Copied from ProductController
     */
    protected function updateProduct(ProductInterface $product, array $data): void
    {
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode()
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        // don't filter during creation, because identifier is needed
        // but not sent by the frontend during creation (it sends the sku in the values)
        if (null !== $product->getId() && $product->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $this->productUpdater->update($product, $data);
    }
}