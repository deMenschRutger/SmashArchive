<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;

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
     * Returns a list of available countries.
     *
     * @return Country[]
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/", name="api_countries_overview")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Countries")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the countries were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Country::class))
     *     )
     * )
     */
    public function indexAction()
    {
        return $this->getRepository('App:Country')->findAll();
    }
}
