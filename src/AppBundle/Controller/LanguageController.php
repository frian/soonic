<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Language controller.
 *
 * @Route("language")
 */
class LanguageController extends Controller
{
    /**
     * Lists all language entities.
     *
     * @Route("/", name="language_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $languages = $em->getRepository('AppBundle:Language')->findAll();

        return $this->render('language/index.html.twig', array(
            'languages' => $languages,
        ));
    }

    /**
     * Finds and displays a language entity.
     *
     * @Route("/{id}", name="language_show")
     * @Method("GET")
     */
    public function showAction(Language $language)
    {

        return $this->render('language/show.html.twig', array(
            'language' => $language,
        ));
    }
}
