<?php

declare(strict_types = 1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class HashedType extends StringType
{
    const NAME = 'hashed';

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return hash('sha256', $value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
