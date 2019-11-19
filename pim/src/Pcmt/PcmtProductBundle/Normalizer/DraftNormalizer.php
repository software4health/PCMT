<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pcmt\PcmtProductBundle\Entity\AttributeChange;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class DraftNormalizer implements NormalizerInterface
{
    /**
     * @var DraftStatusNormalizer
     */
    private $statusNormalizer;
    /**
     * @var AttributeChangeNormalizer
     */
    private $attributeChangeNormalizer;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        AttributeChangeNormalizer $attributeChangeNormalizer
    )
    {
        $this->statusNormalizer = $statusNormalizer;
        $this->attributeChangeNormalizer = $attributeChangeNormalizer;
    }

    /**
     * @param ProductDraftInterface $draft
     * @param null $format
     * @param array $context
     * @return array
     */
    public function normalize($draft, $format = null, array $context = []): array
    {
        $data = [];
        $data['id'] = $draft->getId();
        $productLabel = 'no label';
        /** @var AbstractProductDraft $draft */
        switch (get_class($draft)) {
            case NewProductDraft::class:
                $productLabel = $draft->getProductData()['identifier'] ?? 'no label';
                break;
            case PendingProductDraft::class:
                $product = $draft->getProduct();
                $productLabel = $product ? $product->getIdentifier() : 'no product id';
                break;
        }
        $data['label'] = $productLabel;
        $createdAt = $draft->getCreatedAt();
        $createdAt->format('Y-m-d H:i');
        $data['createdAt'] = $draft->getCreatedAtFormatted();
        $author = $draft->getAuthor();
        $data['author'] = $author ?
            $author->getFirstName() . ' ' . $author->getLastName() : 'no author';
        $data['changes'] = $this->getChanges($draft);
        $data['status'] = $this->statusNormalizer->normalize($draft);

        return $data;
    }

    private function getChanges(ProductDraftInterface $draft): array
    {
        $changes = [];

        $draftData = $draft->getProductData();

        $product = $draft->getProduct();
        foreach ($draftData as $attribute => $value) {
            if ($attribute == "values") {
                foreach ($value as $vAttribute => $valueOfValues) {
                    $v = $valueOfValues[0]["data"] ?? null;
                    $changes[] = $this->createChange($vAttribute, $v, $product);
                }
            } else {
                $changes[] = $this->createChange($attribute, $value, $product);
            }
        }

        $serializer = new Serializer([$this->attributeChangeNormalizer]);
        return $serializer->normalize($changes);
    }

    private function createChange($attribute, $value, $product): AttributeChange
    {
        return new AttributeChange(
            $attribute,
            (string)($product ? $this->getPreviousValue($product, $attribute) : null),
            is_array($value) ? json_encode($value) : (string)$value
        );
    }

    private function getPreviousValue(ProductInterface $product, $attribute)
    {
        switch ($attribute) {
            case 'family':
                return $product->getFamily()->getCode();
            case 'identifier':
                return $product->getIdentifier();
            default:
                // this may need refactoring in future when creating draft from existing product will be implemented
                $data = $product->getRawValues();
                if (!empty($data["attribute"])) {
                    return $data["attribute"];
                }
                return null;
        }
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDraftInterface;
    }
}