<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Swagger\Annotations as SWG;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api")
 */
class DefaultController
{
    /**
     * Returns information about the status of the API.
     *
     * @return array
     *
     * @Sensio\Method("GET")
     * @Sensio\Route("/")
     *
     * @SWG\Tag(name="Status")
     * @SWG\Response(
     *     response=200,
     *     description="Returned when the status of the API was successfully retrieved.",
     *     @SWG\Items(
     *         @SWG\Property(property="version", type="string", example="1.2.3")
     *     )
     * )
     */
    public function index(): array
    {
        return [
            'version' => '0.2.1',
        ];
    }
}
