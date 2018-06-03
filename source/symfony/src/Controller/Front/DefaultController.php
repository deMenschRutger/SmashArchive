<?php

declare(strict_types = 1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }
}
