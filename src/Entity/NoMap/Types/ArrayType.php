<?php

// src/Entity/NoMap/Types/ArrayType.php

namespace App\Entity\NoMap\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

use function unserialize;

/**
 * Type that maps a PHP array to a clob SQL type.
 */
class ArrayType extends Type
{
    public const ARRAY = 'array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return \serialize($value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if (empty($value)) {
            return [];
        }

        $value = \is_resource($value) ? \stream_get_contents($value) : $value;

        try {
            // return json_decode(unserialize(json_decode($value)), true);
            return \unserialize($value);
        } finally {
            \restore_error_handler();
        }
    }

    public function getName()
    {
        return self::ARRAY;
    }
}
