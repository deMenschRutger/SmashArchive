<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Player\DetailsCommand;
use App\Bus\Command\Player\HeadToHeadCommand;
use App\Bus\Command\Player\OverviewCommand;
use App\Bus\Command\Player\RanksCommand;
use App\Bus\Command\Player\SetsCommand;
use App\Entity\Profile;
use App\Form\Player\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Exception\FormValidationException;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/players")
 */
class PlayerController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CommandBus             $bus
     */
    public function __construct(EntityManagerInterface $entityManager, CommandBus $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @param Request $request
     *
     * @return PaginatedResponseInterface
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/", name="api_players_overview")
     */
    public function indexAction(Request $request)
    {
        $tag = $request->query->get('tag');
        $location = $request->query->get('location');
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new OverviewCommand($tag, $location, $page, $limit);
        $pagination = $this->bus->handle($command);

        $this->setSerializationGroups('players_overview');

        return $this->buildPaginatedResponse($pagination);
    }

    /**
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/", name="api_players_details")
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_details');

        return $sets;
    }

    /**
     * @param Request $request
     * @param string  $slug
     *
     * @return array|OffsetPaginatedResponse
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/sets/", name="api_players_sets")
     */
    public function setsAction(Request $request, $slug)
    {
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new SetsCommand($slug, null, false, $page, $limit);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_sets');

        return $this->buildPaginatedResponse($sets);
    }

    /**
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/ranks/", name="api_players_ranks")
     *
     * @TODO Add pagination.
     */
    public function ranksAction($slug)
    {
        $command = new RanksCommand($slug);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('players_ranks');

        return $sets;
    }

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{playerOneSlug}/head-to-head/{playerTwoSlug}/", name="api_players_head_to_head")
     */
    public function headToHeadAction($playerOneSlug, $playerTwoSlug)
    {
        $command = new HeadToHeadCommand($playerOneSlug, $playerTwoSlug);

        return $this->bus->handle($command);
    }

    /**
     * @param Request $request
     *
     * @return Profile
     *
     * @Sensio\Method("POST")
     * @Sensio\Route("/", name="api_players_add")
     * @Sensio\IsGranted("ROLE_ADMIN")
     */
    public function addAction(Request $request)
    {
        $profile = new Profile();

        $this->validateForm($request, ProfileType::class, $profile);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    /**
     * @param Request $request
     * @param string  $slug
     *
     * @return Profile
     *
     * @Sensio\Method("PUT")
     * @Sensio\Route("/{slug}/", name="api_players_update")
     * @Sensio\IsGranted("ROLE_ADMIN")
     */
    public function updateAction(Request $request, $slug)
    {
        $profile = $this->getRepository('App:Profile')->findOneBy([
            'slug' => $slug,
        ]);

        if (!$profile instanceof Profile) {
            throw new NotFoundHttpException('The player profile could not be found.');
        }

        $this->validateForm($request, ProfileType::class, $profile);

        $this->entityManager->flush();

        return $profile;
    }
}
