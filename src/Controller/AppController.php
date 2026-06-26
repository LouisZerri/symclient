<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    // Sert l'application React pour toutes les URLs hors /api et /build,
    // afin que react-router (BrowserRouter) gère le routage côté client,
    // y compris au rafraîchissement (ex. /customers, /invoices/5).
    #[Route(
        '/{reactRouting}',
        name: 'app',
        requirements: ['reactRouting' => '^(?!api|build|_(profiler|wdt)).+'],
        defaults: ['reactRouting' => null],
        priority: -10,
    )]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }
}
