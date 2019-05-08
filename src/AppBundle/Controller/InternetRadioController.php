<?php

namespace AppBundle\Controller;

use AppBundle\Entity\InternetRadio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Internetradio controller.
 *
 * @Route("radio")
 */
class InternetRadioController extends Controller
{
    /**
     * Lists all internetRadio entities.
     *
     * @Route("/", name="radio_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $internetRadios = $em->getRepository('AppBundle:InternetRadio')->findAll();

        return $this->render('internetradio/index.html.twig', array(
            'internetRadios' => $internetRadios,
        ));
    }

    /**
     * Creates a new internetRadio entity.
     *
     * @Route("/new", name="radio_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $internetRadio = new Internetradio();
        $form = $this->createForm('AppBundle\Form\InternetRadioType', $internetRadio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($internetRadio);
            $em->flush();

            return $this->redirectToRoute('radio_show', array('id' => $internetRadio->getId()));
        }

        return $this->render('internetradio/new.html.twig', array(
            'internetRadio' => $internetRadio,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a internetRadio entity.
     *
     * @Route("/{id}", name="radio_show")
     * @Method("GET")
     */
    public function showAction(InternetRadio $internetRadio)
    {
        $deleteForm = $this->createDeleteForm($internetRadio);

        return $this->render('internetradio/show.html.twig', array(
            'internetRadio' => $internetRadio,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing internetRadio entity.
     *
     * @Route("/{id}/edit", name="radio_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, InternetRadio $internetRadio)
    {
        $deleteForm = $this->createDeleteForm($internetRadio);
        $editForm = $this->createForm('AppBundle\Form\InternetRadioType', $internetRadio);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('radio_edit', array('id' => $internetRadio->getId()));
        }

        return $this->render('internetradio/edit.html.twig', array(
            'internetRadio' => $internetRadio,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a internetRadio entity.
     *
     * @Route("/{id}", name="radio_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, InternetRadio $internetRadio)
    {
        $form = $this->createDeleteForm($internetRadio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($internetRadio);
            $em->flush();
        }

        return $this->redirectToRoute('radio_index');
    }

    /**
     * Creates a form to delete a internetRadio entity.
     *
     * @param InternetRadio $internetRadio The internetRadio entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(InternetRadio $internetRadio)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('radio_delete', array('id' => $internetRadio->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
