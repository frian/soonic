<?php

namespace App\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Album browsing controller.
 */
#[Route(path: '/album')]
class AlbumController extends AbstractController
{
    /**
     * Displays albums list (full page or AJAX fragment).
     */
    #[Route(path: '/', name: 'album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, Request $request): Response
    {
        $albums = $albumRepository->findAll();

        if ($request->isXmlHttpRequest()) {
            return $this->render('album/index-content.html.twig', [
                'albums' => $albums,
            ]);
        }

        return $this->render('album/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    /**
     * Displays one album (full page or AJAX fragment).
     */
    #[Route(path: '/{id}', name: 'album_show', methods: ['GET'])]
    public function show(?Album $album, Request $request): Response
    {
        if (!$album) {
            throw $this->createNotFoundException('Album not found.');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('album/show-content.html.twig', ['album' => $album]);
        }

        return $this->render('album/show.html.twig', ['album' => $album]);
    }
}
