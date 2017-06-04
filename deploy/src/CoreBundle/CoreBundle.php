<?php

declare(strict_types = 1);

namespace CoreBundle;

use CoreBundle\Doctrine\DBAL\Types\Encrypted;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Tournament;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sluggable\Util\Urlizer;
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

        /** @var SluggableListener $sluggableListener */
        $sluggableListener = $this->container->get('gedmo.listener.sluggable');
        $sluggableListener->setUrlizer([$this, 'urlize']);
    }

    /**
     * @param string $text
     * @param bool   $separatorUsed
     * @param mixed  $objectBeingSlugged
     * @return string
     *
     * @TODO Move this to a separate file.
     */
    public function urlize($text, $separatorUsed, $objectBeingSlugged)
    {
        $slug = Urlizer::urlize($text);

        if (strlen($slug) === 0) {
            if ($objectBeingSlugged instanceof Tournament) {
                return 'tournament-'.uniqid();
            } elseif ($objectBeingSlugged instanceof Player) {
                return 'player-'.uniqid();
            }
        }

        return $slug;
    }
}
