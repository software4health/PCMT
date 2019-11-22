<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;

class ConcatenatedAttributeProductValuesUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    public function __construct(PropertySetterInterface $propertySetter)
    {
        $this->propertySetter = $propertySetter;
    }

    public function update($entityWithValues, array $data, array $options = []): void
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithValues),
                EntityWithValuesInterface::class
            );
        }

        $this->checkValuesData($data);
        $this->updateEntityWithValues($entityWithValues, $data);
    }

    /**
     * @Override EntityWthValuesUpdater
     * overwrite with new value each call
     */
    protected function updateEntityWithValues(EntityWithValuesInterface $entityWithValues, array $values): void
    {
        foreach ($values as $code => $value) {
            foreach ($value as $data) {
                $hasData = !('' === $data['data'] || [] === $data['data'] || null === $data['data']);
                if (!$hasData) {
                    return;
                }
                $options = [
                    'locale' => $data['locale'],
                    'scope' => $data['scope'],
                ];
                $this->propertySetter->setData($entityWithValues, $code, $data['data'], $options);
            }
        }
    }

    /**
     * Check the structure of the given entity with values.
     *
     * @param mixed $entityWithValues
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValuesData($entityWithValues): void
    {
        if (!is_array($entityWithValues)) {
            throw InvalidPropertyTypeException::arrayExpected('values', static::class, $entityWithValues);
        }

        foreach ($entityWithValues as $code => $values) {
            if (!is_array($values)) {
                throw InvalidPropertyTypeException::arrayExpected($code, static::class, $values);
            }

            foreach ($values as $value) {
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $code,
                        'one of the values is not an array.',
                        static::class,
                        $values
                    );
                }

                if (!array_key_exists('locale', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'locale', static::class, $value);
                }

                if (!array_key_exists('scope', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'scope', static::class, $value);
                }

                if (!array_key_exists('data', $value)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($code, 'data', static::class, $value);
                }

                if (null !== $value['locale'] && !is_string($value['locale'])) {
                    $message = 'Property "%s" expects a value with a string as locale, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $value['locale'],
                        static::class,
                        sprintf($message, $code, gettype($value['locale'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }

                if (null !== $value['scope'] && !is_string($value['scope'])) {
                    $message = 'Property "%s" expects a value with a string as scope, "%s" given.';

                    throw new InvalidPropertyTypeException(
                        $code,
                        $value['scope'],
                        static::class,
                        sprintf($message, $code, gettype($value['scope'])),
                        InvalidPropertyTypeException::STRING_EXPECTED_CODE
                    );
                }
            }
        }
    }
}