<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Library controller.
 */
class LibraryController extends Controller
{
    /**
     * Show library page.
     *
     * @Route("/", name="library")
     * @Method("GET")
     */
    public function showLibraryAction() {

        $em = $this->getDoctrine()->getManager();

        // -- get artist list for left panel
        $artists = $em->getRepository('AppBundle:Artist')->findAll();

        return $this->render('library/screen.html.twig', array(
            'artists' => $artists,
        ));
    }


    /**
     * List filtered artist entities.
     *
     * @Route("/artist/filter/", name="artist_filter_all")
     * @Route("/artist/filter/{filter}", name="artist_filter")
     * @Method("GET")
     */
    public function filterArtistAction($filter = null) {

        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findByFilter($filter);

        return $this->render('library/artist-nav-list.html.twig', array(
            'artists' => $artists,
        ));
    }


    /**
     * Find albums from an artist.
     *
     * @Route("/albums/{id}", name="artist_albums")
     * @Method("GET")
     */
    public function showArtistAlbumsAction(Artist $artist) {

        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository('AppBundle:Album')->findByArtist($artist->getName());

        return $this->render('library/album-nav-list.html.twig', array(
            'albums' => $albums,
        ));
    }


    /**
     * Find songs from an album from an artist.
     *
     * @Route("/songs/{artist}/{album}", name="artist_albums_songs")
     * @Method("GET")
     */
    public function showAlbumsSongsAction($artist, $album) {

        $em = $this->getDoctrine()->getManager();

        $artist = \preg_replace("/^'|'$/", '', $artist);

        $artist = $em->getRepository('AppBundle:Artist')->findOneByName($artist);

        $albums = $em->getRepository('AppBundle:MediaFile')->findByAlbum($artist->getId(), $album);

        return $this->render('common/songs-list.html.twig', array(
            'mediaFiles' => $albums,
        ));
    }
}
