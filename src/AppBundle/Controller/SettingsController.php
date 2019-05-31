<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use AppBundle\Entity\Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // -- get collection infos
        $query = "select max(id) from media_file";
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $numFiles = $statement->fetch()['max(id)'];
        if ($numFiles === null) {
            $numFiles = 0;
        }

        // -- get config form
        $config = $em->getRepository('AppBundle:Config')->find(1);
        $editForm = $this->createForm('AppBundle\Form\ConfigType', $config);
        $editForm->handleRequest($request);



        return $this->render('settings/index.html.twig', array(
            'numFiles' => $numFiles,
            'edit_form' => $editForm->createView()
            // 'dump' => $config
        ));
    }
}
