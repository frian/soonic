<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MediaFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Mediafile controller.
 *
 * @Route("mediafile")
 */
class MediaFileController extends Controller
{
    /**
     * Lists all mediaFile entities.
     *
     * @Route("/", name="mediafile_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $mediaFiles = $em->getRepository('AppBundle:MediaFile')->findAll();

        return $this->render('common/songs-list.html.twig', array(
            'mediaFiles' => $mediaFiles,
        ));
    }

    /**
     * Finds and displays a mediaFile entity.
     *
     * @Route("/{id}", name="mediafile_show")
     * @Method("GET")
     */
    public function showAction(MediaFile $mediaFile)
    {

        return $this->render('mediafile/show.html.twig', array(
            'mediaFile' => $mediaFile,
        ));
    }
}
