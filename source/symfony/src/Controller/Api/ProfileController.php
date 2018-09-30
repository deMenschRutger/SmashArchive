<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Bus\Command\Profile\DetailsCommand;
use App\Bus\Command\Profile\HeadToHeadCommand;
use App\Bus\Command\Profile\OverviewCommand;
use App\Bus\Command\Profile\RanksCommand;
use App\Bus\Command\Profile\SetsCommand;
use App\Entity\Profile;
use App\Entity\Rank;
use App\Entity\Set;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\CommandBus;
use MediaMonks\RestApi\Response\OffsetPaginatedResponse;
use MediaMonks\RestApi\Response\PaginatedResponseInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/profiles")
 */
class ProfileController extends AbstractController
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
     * Returns a list of profiles.
     *
     * @param Request $request
     *
     * @return PaginatedResponseInterface
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/", name="api_profiles_overview")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the profiles were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Profile::class, groups={"profiles_overview"}))
     *     )
     * )
     */
    public function indexAction(Request $request)
    {
        $tag = $request->query->get('tag');
        $location = $request->query->get('location');
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new OverviewCommand($tag, $location, $page, $limit);
        $pagination = $this->bus->handle($command);

        $this->setSerializationGroups('profiles_overview');

        return $this->buildPaginatedResponse($pagination);
    }

    /**
     * Returns the details of a specific profile.
     *
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/", name="api_profiles_details")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the profile details were successfully retrieved.",
     *     @SWG\Items(ref=@Model(type=Profile::class, groups={"profiles_details"}))
     * )
     */
    public function detailsAction($slug)
    {
        $command = new DetailsCommand($slug);
        $profile = $this->bus->handle($command);

        $this->setSerializationGroups('profiles_details');

        return $profile;
    }

    /**
     * Returns all sets that are associated with this profile.
     *
     * @param Request $request
     * @param string  $slug
     *
     * @return array|OffsetPaginatedResponse
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/sets/", name="api_profiles_sets")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the sets were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Set::class, groups={"profiles_sets"}))
     *     )
     * )
     */
    public function setsAction(Request $request, $slug)
    {
        $page = $request->query->getInt('page', null);
        $limit = $request->query->getInt('limit', null);

        $command = new SetsCommand($slug, null, false, $page, $limit);
        $sets = $this->bus->handle($command);

        $this->setSerializationGroups('profiles_sets');

        return $this->buildPaginatedResponse($sets);
    }

    /**
     * Returns all ranks that are associated with this profile.
     *
     * @param string $slug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{slug}/ranks/", name="api_profiles_ranks")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the ranks were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Rank::class, groups={"profiles_ranks"}))
     *     )
     * )
     *
     * @TODO Add pagination.
     */
    public function ranksAction($slug)
    {
        $command = new RanksCommand($slug);
        $ranks = $this->bus->handle($command);

        $this->setSerializationGroups('profiles_ranks');

        return $ranks;
    }

    /**
     * Returns the head-to-head score between two profiles.
     *
     * @param string $profileOneSlug
     * @param string $profileTwoSlug
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/{profileOneSlug}/head-to-head/{profileTwoSlug}/", name="api_profiles_head_to_head")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the head-to-head score was successfully retrieved."
     * )
     */
    public function headToHeadAction($profileOneSlug, $profileTwoSlug)
    {
        $command = new HeadToHeadCommand($profileOneSlug, $profileTwoSlug);

        return $this->bus->handle($command);
    }

    /**
     * Adds a new profile.
     *
     * @param Request $request
     *
     * @return Profile
     *
     * @Sensio\Method("POST")
     * @Sensio\Route("/", name="api_profiles_add")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Parameter(
     *     in="body",
     *     name="status",
     *     @Model(type=ProfileType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the profile details were successfully updated.",
     *     @SWG\Items(ref=@Model(type=Profile::class, groups={"profiles_details"}))
     * )
     */
    public function addAction(Request $request)
    {
        $profile = new Profile();

        $this->validateForm($request, ProfileType::class, $profile, true);

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    /**
     * Updates specific properties of an existing profile.
     *
     * @param Request $request
     * @param string  $slug
     *
     * @return Profile
     *
     * @Sensio\Method("PATCH")
     * @Sensio\Route("/{slug}/", name="api_profiles_update")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Parameter(
     *     in="body",
     *     name="status",
     *     @Model(type=ProfileType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the profile details were successfully updated.",
     *     @SWG\Items(ref=@Model(type=Profile::class, groups={"profiles_details"}))
     * )
     */
    public function updateAction(Request $request, $slug)
    {
        $command = new DetailsCommand($slug);
        $profile = $this->bus->handle($command);

        $this->validateForm($request, ProfileType::class, $profile);

        $this->entityManager->flush();

        $this->setSerializationGroups('profiles_details');

        return $profile;
    }

    /**
     * Deletes an existing profile.
     *
     * @param string $slug
     *
     * @return bool
     *
     * @Sensio\Method("DELETE")
     * @Sensio\Route("/{slug}/", name="api_profiles_delete")
     * @Sensio\IsGranted("ROLE_ADMIN")
     *
     * @SWG\Tag(name="Profiles")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the profile was successfully deleted."
     * )
     */
    public function deleteAction($slug)
    {
        $command = new DetailsCommand($slug);
        $profile = $this->bus->handle($command);

        $this->entityManager->remove($profile);
        $this->entityManager->flush();

        return true;
    }
}
