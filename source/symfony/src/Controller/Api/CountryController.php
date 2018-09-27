<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/countries")
 */
class CountryController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Country[]
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/", name="api_countries_overview")
     * @Sensio\IsGranted("ROLE_ADMIN")
     */
    public function indexAction()
    {
        return $this->getRepository('App:Country')->findAll();
    }
}
