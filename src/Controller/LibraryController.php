<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
    public function showAlbumsSongs(AlbumRepository $albumRepository, SongRepository $songRepository, ?Artist $artist = null, string $albumSlug): Response
    {
        $response = new Response();

        if (!$artist) {
            $response->setStatusCode(404);
            $songs = [];
        } else {
            $album = $albumRepository->findOneBy(['albumSlug' => $albumSlug]);
            if (!$album) {
                $response->setStatusCode(404);
                $songs = [];
            } else {
                $songs = $songRepository->findByArtistAndAlbum($artist->getName(), $album->getName());
            }
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
    public function filterArtist(ArtistRepository $artistRepository, ?string $filter = null): Response
    {
        $artists = $artistRepository->findByFilter($filter);

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
    public function randomSongs(SongRepository $songRepository, int $number = 20): Response
    {   
        $songs = $songRepository->getRandom($number);

        return $this->render('common/songs-list.html.twig', [
            'songs' => $songs,
        ]);
    }
}
