<?php

namespace App\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/album')]
class AlbumController extends AbstractController
{
    /**
     * Method index
     *
     * @param AlbumRepository $albumRepository
     * @param Request $request
     *
     * @return Response
     */
    #[Route(path: '/', name: 'album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('album/index-content.html.twig', [
                'albums' => $albumRepository->findAll(),
            ]);
        }

        return $this->render('album/index.html.twig', [
            'albums' => $albumRepository->findAll(),
        ]);
    }

    /**
     * Method show
     *
     * @param Album $album
     * @param Request $request
     *
     * @return Response
     */
    #[Route(path: '/{id}', name: 'album_show', methods: ['GET'])]
    public function show(Album $album = null, Request $request): Response
    {
        $response = new Response();

        if (!$album) {
            $response->setStatusCode(404);
        }

        $content = $this->renderView('album/show.html.twig', ['album' => $album]);

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('album/show-content.html.twig', ['album' => $album]);
        }

        $response->setContent($content);
        return $response;
    }
}
