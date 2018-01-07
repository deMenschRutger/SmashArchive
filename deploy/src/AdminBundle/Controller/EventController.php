<?php

declare(strict_types = 1);

namespace AdminBundle\Controller;

use CoreBundle\Entity\Entrant;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventController extends AbstractController
{
    /**
     * @param Request $request
     * @param string  $id
     * @return Response
     */
    public function entrantsAction(Request $request, $id)
    {
        $name = $request->query->get('q');
        $page = $request->query->getInt('_page');
        $limit = $request->query->getInt('_per_page');

        $paginator = $this->get('knp_paginator');
        $query = $this->getEntityManager()->getRepository('CoreBundle:Entrant')->findByEventId($id, $name);
        $entrants = $paginator->paginate($query, $page, $limit);

        $items = [];

        /** @var Entrant $entrant */
        foreach ($entrants as $entrant) {
            $items[] = [
                'id' => $entrant->getId(),
                'label' => $entrant->getExpandedName(),
            ];
        }

        return new JsonResponse([
            'status' => 'OK',
            'more' => $entrants->getPageCount() !== $page,
            'items' => $items,
        ]);
    }
}
