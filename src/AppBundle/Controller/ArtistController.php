<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Artist controller.
 *
 * @Route("artist")
 */
class ArtistController extends Controller
{
    /**
     * Lists all artist entities.
     *
     * @Route("/", name="artist_index")
     * @Method("GET")
     */
    public function showArtistAction()
    {
        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findAll();

        return $this->render('artist/index.html.twig', array(
            'artists' => $artists,
        ));
    }


    /**
     * Lists filtered artist entities.
     *
     * @Route("/filter/", name="artist_filter_all")
     * @Route("/filter/{filter}", name="artist_filter")
     * @Method("GET")
     */
    public function filterArtistAction($filter = null)
    {
        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findByFilter($filter);

        return $this->render('artist/artist-list.html.twig', array(
            'artists' => $artists,
        ));
    }



    /**
     * Finds songs from an artist.
     *
     * @Route("/{id}", name="artist_show")
     * @Method("GET")
     */
    public function showArtistSongsAction(Artist $artist)
    {
        $em = $this->getDoctrine()->getManager();

        $songs = $em->getRepository('AppBundle:MediaFile')->findByArtist($artist->getName());

        return $this->render('artist/show.html.twig', array(
            'songs' => $songs,
        ));
    }

    /**
     * Finds albums from an artist.
     *
     * @Route("/albums/{id}", name="artist_albums")
     * @Method("GET")
     */
    public function showArtistAlbumsAction(Artist $artist)
    {
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository('AppBundle:Album')->findByArtist($artist->getName());

        return $this->render('album/album-list.html.twig', array(
            'albums' => $albums,
        ));
    }


    /**
     * Finds songs from an album from an artist.
     *
     * @Route("/songs/{name}/{album}", name="artist_albums_songs")
     * @Method("GET")
     *
     */
    public function showArtistAlbumsSongsAction(Artist $artist, $album)
    {
        $em = $this->getDoctrine()->getManager();

        $albums = $em->getRepository('AppBundle:MediaFile')->findByAlbum($artist->getName(), $album);

        return $this->render('mediafile/index.html.twig', array(
            'mediaFiles' => $albums,
        ));
    }
}
