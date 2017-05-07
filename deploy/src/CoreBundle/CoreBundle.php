<?php

declare(strict_types = 1);

namespace CoreBundle;

use CoreBundle\Doctrine\DBAL\Types\Encrypted;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zend\Crypt\BlockCipher;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class CoreBundle extends Bundle
{
    /**
     * @return void
     */
    public function boot()
    {
        /** @var BlockCipher $encryption */
        $encryption = $this->container->get('core.utils.encryption');

        Encrypted::setEncryption($encryption);
    }
}
