<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Album controller.
 *
 * @Route("settings")
 */
class SettingsController extends Controller
{
    /**
     * Show settings page
     *
     * @Route("/", name="settings_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = "select max(id) from media_file";
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $numFiles = $statement->fetch()['max(id)'];
        if ($numFiles === null) {
            $numFiles = 0;
        }

        return $this->render('settings/index.html.twig', array(
            'numFiles' => $numFiles,
        ));
    }
}
