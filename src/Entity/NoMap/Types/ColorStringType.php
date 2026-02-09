<?php

// src/Entity/NoMap/Types/ColorStringType.php

namespace App\Entity\NoMap\Types;

use App\Controller\_Utils\Attributes;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps a PHP array to a clob SQL type.
 */
class ColorStringType extends Type
{
    use Attributes;
    public const STRING_ARRAY = 'color_string';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): string
    {
        if (null === $value) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        foreach (\explode('-', $value) as $i => $val) {
            $color[] = $this->fullName($val);
        }

        return \implode(' | ', $color);
    }

    public function getName()
    {
        return self::COLOR_STRING;
    }
}
