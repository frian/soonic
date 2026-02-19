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

/**
 * Main music library navigation controller.
 */
class LibraryController extends AbstractController
{
    /**
     * Main library page (full or partial HTML for AJAX refresh).
     */
    #[Route(path: '/', name: 'library', methods: ['GET'])]
    public function library(ArtistRepository $artistRepository, Request $request): Response
    {
        $artists = $artistRepository->findAll();

        if ($request->isXmlHttpRequest()) {
            return $this->render('library/index-content.html.twig', [
                'artists' => $artists,
            ]);
        }

        return $this->render('library/index.html.twig', [
            'artists' => $artists,
        ]);
    }

    /**
     * Returns albums navigation list for one artist.
     */
    #[Route(path: '/albums/{artistSlug:artist}', name: 'artist_albums', methods: ['GET'])]
    public function showArtistAlbums(Artist $artist): Response
    {
        return $this->render('library/album-nav-list.html.twig', [
            'albums' => $artist->getAlbums(),
            'artist' => $artist->getArtistSlug(),
        ]);
    }

    /**
     * Returns songs list for one artist/album slug pair.
     * Sends 404 when the album does not belong to the artist.
     */
    #[Route(path: '/songs/{artistSlug:artist}/{albumSlug}', name: 'artist_albums_songs', methods: ['GET'])]
    public function showAlbumsSongs(AlbumRepository $albumRepository, SongRepository $songRepository, string $albumSlug, Artist $artist): Response
    {
        $album = $albumRepository->findOneByArtistAndAlbumSlug($artist->getArtistSlug(), $albumSlug);
        if (!$album) {
            return $this->render('common/songs-list.html.twig', [
                'songs' => [],
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        return $this->render('common/songs-list.html.twig', [
            'songs' => $songRepository->findByArtistAndAlbum($artist->getName(), $album->getName()),
        ]);
    }

    /**
     * Returns filtered artists list for the left navigation.
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
     * Returns a random songs selection.
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
