<?php

namespace Pcmt\PcmtAttributeBundle\Updater;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater as BaseAttributeUpdater;
use Pcmt\PcmtTranslationBundle\Updater\TranslatableUpdater;
use Pcmt\PcmtAttributeBundle\Extension\PcmtAttributeManager;
use Pcmt\PcmtAttributeBundle\Entity\ConcatenatedAttribute;

/**
 * @override: Handle localizable attribute description when updating an attribute
 *
 * Class AttributeUpdater
 *
 * @author                 Benjamin Hil <benjamin.hil@dnd.fr>
 * @copyright              Copyright (c) 2018 Agence Dn'D
 * @license                http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link                   http://www.dnd.fr/
 */
class AttributeUpdater extends BaseAttributeUpdater
{

  /** @var TranslatableUpdater */
  protected $translatableUpdater;

  /** @var PcmtAttributeManager $pcmtAttributesManager */
  protected $pcmtAttributesManager;

  public function __construct
  (
      AttributeGroupRepositoryInterface $attrGroupRepo,
      LocaleRepositoryInterface $localeRepository,
      AttributeTypeRegistry $registry,
      \Akeneo\Tool\Component\Localization\TranslatableUpdater $translatableUpdater,
      PcmtAttributeManager $pcmtAttributesManager,
      array $properties
  )
  {
      $this->pcmtAttributesManager = $pcmtAttributesManager;
      parent::__construct($attrGroupRepo, $localeRepository, $registry, $translatableUpdater, $properties);
  }

    /**
   * {@inheritdoc}
   */
  public function update($attribute, array $data, array $options = [])
  {
    if (!$attribute instanceof AttributeInterface) {
      throw InvalidObjectException::objectExpected(
        ClassUtils::getClass($attribute),
        AttributeInterface::class
      );
    }
    foreach ($data as $field => $value) {
      $this->validateDataType($field, $value);
      $this->setData($attribute, $field, $value);
    }

    return $this;
  }

  /**
   * Validate the data type of a field.
   *
   * @param string $field
   * @param mixed $data
   *
   * @throws InvalidPropertyTypeException
   * @throws UnknownPropertyException
   */
  protected function validateDataType($field, $data)
  {
    if (in_array($field, ['labels', 'available_locales', 'allowed_extensions', 'descriptions', 'concatenated'])) {

      if (!is_array($data)) {
        throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
      }

      foreach ($data as $key => $value) {
        if (null !== $value && !is_scalar($value)) {
          throw InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            sprintf('one of the "%s" values is not a scalar', $field),
            static::class,
            $data
          );
        }
      }
    } elseif (in_array(
      $field,
      [
        'code',
        'type',
        'group',
        'unique',
        'useable_as_grid_filter',
        'metric_family',
        'default_metric_unit',
        'reference_data_name',
        'max_characters',
        'validation_rule',
        'validation_regexp',
        'wysiwyg_enabled',
        'number_min',
        'number_max',
        'decimals_allowed',
        'negative_allowed',
        'date_min',
        'date_max',
        'max_file_size',
        'minimum_input_length',
        'sort_order',
        'localizable',
        'scopable',
        'required',
        'auto_option_sorting',
        'concatenated'
      ]
    )) {
      if (null !== $data && !is_scalar($data)) {
        throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
      }
    } else {
      throw UnknownPropertyException::unknownProperty($field);
    }
  }

  /**
   * @param AttributeInterface $attribute
   * @param string $field
   * @param mixed $data
   */
  protected function setData(AttributeInterface $attribute, $field, $data)
  {
    switch ($field) {
      case 'type':
        $this->setType($attribute, $data);
        break;
      case 'labels':
        $this->translatableUpdater->update($attribute, $data);
        break;
      // Add @DND
      case 'descriptions':
        $this->translatableUpdater->updateDescription($attribute, $data); // update localizable attribute description fields
        break;
      // / Add @DND
      case 'group':
        $this->setGroup($attribute, $data);
        break;
      case 'available_locales':
        $this->setAvailableLocales($attribute, $field, $data);
        break;
      case 'date_min':
        $this->validateDateFormat('date_min', $data);
        $date = $this->getDate($data);
        $attribute->setDateMin($date);
        break;
      case 'date_max':
        $this->validateDateFormat('date_max', $data);
        $date = $this->getDate($data);
        $attribute->setDateMax($date);
        break;
      case 'allowed_extensions':
        $attribute->setAllowedExtensions(implode(',', $data));
        break;
      case 'auto_option_sorting':
        $attribute->setProperty('auto_option_sorting', $data);
        break;
        case 'concatenated':
        $this->pcmtAttributesManager::decorateAttributeInstance(ConcatenatedAttribute::class, $attribute, $field, $data);
        break;
      default:
        $this->setValue($attribute, $field, $data);
    }
  }
}
