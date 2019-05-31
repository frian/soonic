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
        $tables = array('media_file', 'artist', 'album');
        $infos = array();

        foreach ($tables as $table) {
            $query = "select max(id) from $table";
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();
            $result = $statement->fetch()['max(id)'];
            if ($result === null) {
                $result = 0;
            }
            \array_push($infos, $result);
        }

        // -- get config form
        $config = $em->getRepository('AppBundle:Config')->find(1);
        $editForm = $this->createForm('AppBundle\Form\ConfigType', $config);
        $editForm->handleRequest($request);

        return $this->render('settings/index.html.twig', array(
            'infos' => $infos,
            'edit_form' => $editForm->createView()
        ));
    }
}
