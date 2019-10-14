<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Comparator;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

class ConcatenatedComparator implements ComparatorInterface
{
    /** @var array $types */
    protected $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function supports($data): bool
    {
        return in_array($data, $this->types);
    }

    public function compare($data, $originals): ?string
    {
        $default = ['locale' => null, 'scope' => null, 'data' => null];
        $originals = array_merge($default, $originals);

        return (string) $data['data'] === (string) $originals['data'] ? $data : null;
    }
}