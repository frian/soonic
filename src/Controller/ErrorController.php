<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route(path: '/error/{statusCode}', name: 'app_error_show', methods: ['GET'], requirements: ['statusCode' => '[1-5][0-9][0-9]'])]
    public function show(int $statusCode): Response
    {
        if ($statusCode < 400 || $statusCode > 599) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Unknown Error',
        ], new Response('', $statusCode));
    }
}
