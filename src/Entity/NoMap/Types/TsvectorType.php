<?php

// src/Entity/NoMap/Types/TsvectorType.php

namespace App\Entity\NoMap\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TsvectorType extends Type
{
    public function getName()
    {
        return 'tsvector';
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'TSVECTOR';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('to_tsvector(%s)', $sqlExpr);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        return $value;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
