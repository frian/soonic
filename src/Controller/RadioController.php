<?php

namespace App\Controller;

use App\Entity\Radio;
use App\Form\RadioType;
use App\Repository\RadioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/radio')]
class RadioController extends AbstractController
{
    #[Route(path: '/', name: 'radio_index', methods: ['GET'])]
    public function index(RadioRepository $radioRepository): Response
    {
        return $this->render('radio/index.html.twig', [
            'radios' => $radioRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'radio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $radio = new Radio();
        $form = $this->createForm(RadioType::class, $radio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($radio);
            $entityManager->flush();
            $this->addFlash('success', 'Radio added successfully.');

            return $this->redirectToRoute('radio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('radio/new.html.twig', [
            'radio' => $radio,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'radio_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Radio $radio): Response
    {
        return $this->render('radio/show.html.twig', [
            'radio' => $radio,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'radio_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Radio $radio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RadioType::class, $radio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Radio updated successfully.');

            return $this->redirectToRoute('radio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('radio/edit.html.twig', [
            'radio' => $radio,
            'edit_form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'radio_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, Radio $radio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$radio->getId(), $request->request->get('_token'))) {
            $entityManager->remove($radio);
            $entityManager->flush();
            $this->addFlash('success', 'Radio deleted successfully.');
        }

        return $this->redirectToRoute('radio_index', [], Response::HTTP_SEE_OTHER);
    }
}
