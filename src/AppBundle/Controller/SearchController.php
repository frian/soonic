<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Search controller.
 */
class SearchController extends Controller
{
    /**
     * Search.
     *
     * @Route("/search", name="search")
     * @Method({"GET", "POST"})
     */
     public function showSearchAction(Request $request) {

         $form = $this->createFormBuilder()
                 ->add('keyword', TextType::class)
                 ->getForm();

         $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {

             $data = $form->getData();

             $em = $this->getDoctrine()->getManager();

             $results = $em->getRepository('AppBundle:MediaFile')->findByKeyword($data['keyword']);

             return $this->render('mediafile/index.html.twig', array(
                 'mediaFiles' => $results,
             ));
         }

         return $this->render('common/search.html.twig', array(
             'form' => $form->createView(),
         ));
     }
}
