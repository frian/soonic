<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

error_reporting(E_ALL);

class LibraryController extends AbstractController
{
    /**
     * Method library
     * 
     * Shows main page
     *
     * @param ArtistRepository $artistRepository
     * @param Request $request
     *
     * @return Response
     */
    #[Route(path: '/', name: 'library', methods: ['GET'])]    
    public function library(ArtistRepository $artistRepository, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('library/index-content.html.twig', [
                'artists' => $artistRepository->findAll(),
            ]);
        }

        return $this->render('library/index.html.twig', [
            'artists' => $artistRepository->findAll(),
        ]);
    }

    /**
     * Method showArtistAlbums
     * 
     * Find albums from an artist.
     *
     * @param Artist $artist
     *
     * @return Response
     */
    #[Route(path: '/albums/{artistSlug:artist}', name: 'artist_albums', methods: ['GET'])]    
    public function showArtistAlbums(Artist $artist): Response
    {
        $response = new Response();

        if (!$artist) {
            $response->setStatusCode(404);
            $albums = [];
            $artistSlug = '';
        } else {
            $albums = $artist->getAlbums();
            $artistSlug = $artist->getArtistSlug();
        }

        $content = $this->renderView('library/album-nav-list.html.twig', [
            'albums' => $albums,
            'artist' => $artistSlug,
        ]);

        $response->setContent($content);
        return $response;
    }

    /**
     * Method showAlbumsSongs
     *
     * Find songs from an album from an artist.
     * 
     * @param ManagerRegistry $doctrine
     * @param Artist $artist
     * @param $albumSlug $albumSlug
     *
     * @return Response
     */
    #[Route(path: '/songs/{artistSlug:artist}/{albumSlug}', name: 'artist_albums_songs', methods: ['GET'])]    
    public function showAlbumsSongs(ManagerRegistry $doctrine, Artist|null $artist = null, $albumSlug): Response
    {
        $response = new Response();

        if (!$artist) {
            $response->setStatusCode(404);
            $songs = [];
        } else {
            $album = $doctrine->getRepository('App\Entity\Album')->findOneByAlbumSlug($albumSlug);
            $songs = $doctrine->getRepository('App\Entity\Song')->findByArtistAndAlbum($artist->getName(), $album->getName());
        }


        return $this->render('common/songs-list.html.twig', [
            'songs' => $songs,
        ]);
    }

    /**
     * Method filterArtist
     *
     * List filtered artist entities.
     * 
     * @param ManagerRegistry $doctrine
     * @param $filter $filter
     *
     * @return Response
     */
    #[Route(path: '/artist/filter/', name: 'artist_filter_all', methods: ['GET'])]
    #[Route(path: '/artist/filter/{filter}', name: 'artist_filter', methods: ['GET'])]    
    public function filterArtist(ManagerRegistry $doctrine, $filter = null): Response
    {
        $artists = $doctrine->getRepository('App\Entity\Artist')->findByFilter($filter);

        return $this->render('library/artist-nav-list.html.twig', [
            'artists' => $artists,
        ]);
    }

    /**
     * Method randomSongs
     *
     * Load random songs.
     * 
     * @param ManagerRegistry $doctrine
     * @param $number $number
     *
     * @return Response
     */
    #[Route(path: '/songs/random', name: 'random_songs', methods: ['GET'])]    
    public function randomSongs(ManagerRegistry $doctrine, $number = 20): Response
    {   
        $songs = $doctrine->getRepository('App\Entity\Song')->getRandom($number);

        return $this->render('common/songs-list.html.twig', [
            'songs' => $songs,
        ]);
    }
}
