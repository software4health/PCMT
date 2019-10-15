<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Factory\Exception;

class ConcatenatedAttributeException extends \Exception
{
    protected const EXCEPTION_TYPE_MISSING_ATTRIBUTE = 'missing member attribute';

    public static function memberAttributeNonExistent(string $memeberAttributeId): ConcatenatedAttributeException
    {
        return new ConcatenatedAttributeException(
            'The memeber attribute of ID' . $memeberAttributeId . ' does not exist. Cannot fetch value',
            self::EXCEPTION_TYPE_MISSING_ATTRIBUTE,
            true
        );
    }
}