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
     * Method show
     *
     * @param Album $album
     * @param Request $request
     *
     * @return Response
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
