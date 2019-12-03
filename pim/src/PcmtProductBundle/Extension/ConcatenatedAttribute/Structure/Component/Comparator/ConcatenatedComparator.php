<?php

declare(strict_types=1);

namespace PcmtProductBundle\Extension\ConcatenatedAttribute\Structure\Component\Comparator;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

class ConcatenatedComparator implements ComparatorInterface
{
    /** @var mixed[] */
    protected $types = [];

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return in_array($data, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals): ?array
    {
        $default = [
            'locale' => null,
            'scope'  => null,
            'data'   => null,
        ];
        $originals = array_merge($default, $originals);

        return (string) $data['data'] === (string) $originals['data'] ? $data : null;
    }
}