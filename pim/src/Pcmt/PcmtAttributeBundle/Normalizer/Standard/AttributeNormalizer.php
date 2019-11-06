<?php

namespace Pcmt\PcmtAttributeBundle\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeNormalizer as BaseAttributeNormalizer;


class AttributeNormalizer extends BaseAttributeNormalizer
{
  /** @var NormalizerInterface $concatenatedAttributesNormalizer */
  private $concatenatedAttributesNormalizer;

  /** @var NormalizerInterface */
  private $translationNormalizer;

  /** @var NormalizerInterface */
  private $dateTimeNormalizer;

  /** @var array */
  private $properties;

    /**
     * AttributeNormalizer constructor.
     * @param NormalizerInterface $concatenatedNormalizer
     * @param NormalizerInterface $translationNormalizer
     * @param NormalizerInterface $dateTimeNormalizer
     * @param array $properties
     */
  public function __construct(
    NormalizerInterface $concatenatedNormalizer,
    NormalizerInterface $translationNormalizer,
    NormalizerInterface $dateTimeNormalizer,
    array $properties
  ) {
    parent::__construct(
      $translationNormalizer,
      $dateTimeNormalizer,
      $properties);
    $this->concatenatedAttributesNormalizer = $concatenatedNormalizer;
    $this->translationNormalizer = $translationNormalizer;
    $this->dateTimeNormalizer = $dateTimeNormalizer;
    $this->properties = $properties;
  }


  /**
   * {@inheritdoc}
   *
   * @param AttributeInterface $attribute
   */
  public function normalize($attribute, $format = null, array $context = [])
  {
    $normalizedProperties = [];
    foreach ($this->properties as $property) {
      $normalizedProperties[$property] = $attribute->getProperty($property);
    }

    $normalizedAttribute = [
      'code'                   => $attribute->getCode(),
      'type'                   => $attribute->getType(),
      'group'                  => ($attribute->getGroup()) ? $attribute->getGroup()->getCode() : null,
      'unique'                 => (bool) $attribute->isUnique(),
      'useable_as_grid_filter' => (bool) $attribute->isUseableAsGridFilter(),
      'allowed_extensions'     => $attribute->getAllowedExtensions(),
      'metric_family'          => '' === $attribute->getMetricFamily() ? null : $attribute->getMetricFamily(),
      'default_metric_unit'    => '' === $attribute->getDefaultMetricUnit() ?
        null : $attribute->getDefaultMetricUnit(),
      'reference_data_name'    => $attribute->getReferenceDataName(),
      'available_locales'      => $attribute->getAvailableLocaleCodes(),
      'max_characters'         => null === $attribute->getMaxCharacters() ?
        null : (int) $attribute->getMaxCharacters(),
      'validation_rule'        => '' === $attribute->getValidationRule() ? null : $attribute->getValidationRule(),
      'validation_regexp'      => '' === $attribute->getValidationRegexp() ?
        null : $attribute->getValidationRegexp(),
      'wysiwyg_enabled'        => $attribute->isWysiwygEnabled(),
      'number_min'             => null === $attribute->getNumberMin() ?
        null : (string) $attribute->getNumberMin(),
      'number_max'             => null === $attribute->getNumberMax() ?
        null : (string) $attribute->getNumberMax(),
      'decimals_allowed'       => $attribute->isDecimalsAllowed(),
      'negative_allowed'       => $attribute->isNegativeAllowed(),
      'date_min'               => $this->dateTimeNormalizer->normalize($attribute->getDateMin()),
      'date_max'               => $this->dateTimeNormalizer->normalize($attribute->getDateMax()),
      'max_file_size'          => null === $attribute->getMaxFileSize() ?
        null : (string) $attribute->getMaxFileSize(),
      'minimum_input_length'   => null === $attribute->getMinimumInputLength() ?
        null : (int) $attribute->getMinimumInputLength(),
      'sort_order'             => (int) $attribute->getSortOrder(),
      'localizable'            => (bool) $attribute->isLocalizable(),
      'scopable'               => (bool) $attribute->isScopable(),
      'labels'                 => $this->translationNormalizer->normalize($attribute, $format, $context),
      'descriptions'           => $this->translationNormalizer->normalizeDescription($attribute, $context),
      'concatenated'           => $this->concatenatedAttributesNormalizer->normalize($attribute) //not dependent on context
    ];

    return $normalizedAttribute + $normalizedProperties;
  }



}
