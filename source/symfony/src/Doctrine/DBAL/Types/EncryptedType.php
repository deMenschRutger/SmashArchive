<?php

declare(strict_types = 1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;
use Zend\Crypt\BlockCipher;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EncryptedType extends TextType
{
    const NAME = 'encrypted';

    /**
     * @var BlockCipher
     */
    protected static $encryption;

    /**
     * @param BlockCipher $encryption
     */
    public static function setEncryption(BlockCipher $encryption)
    {
        static::$encryption = $encryption;
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return static::$encryption->decrypt($value);
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return static::$encryption->encrypt($value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
