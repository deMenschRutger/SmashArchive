<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\PhaseGroup;
use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/phase-groups")
 */
class PhaseGroupController extends AbstractController
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
     * Returns the sets of a specific phase group.
     *
     * @param string $id
     *
     * @return array
     *
     * @Sensio\Route("/{id}/sets/", name="api_phase_group_sets")
     * @Sensio\Method("GET")
     *
     * @SWG\Tag(name="Phase groups")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the phase group sets were successfully retrieved.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Set::class, groups={"phase_group_sets"}))
     *     )
     * )
     */
    public function setsAction($id)
    {
        $phaseGroup = $this->getRepository('App:PhaseGroup')->find($id);

        if (!$phaseGroup instanceof PhaseGroup) {
            throw new NotFoundHttpException('The phase group could not be found.');
        }

        $this->setSerializationGroups('phase_group_sets');

        return $this->getRepository('App:Set')->findByPhaseGroup($phaseGroup);
    }
}
